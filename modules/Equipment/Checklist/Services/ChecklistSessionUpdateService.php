<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Concerns\SyncsUsersWithNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Throwable;

final class ChecklistSessionUpdateService
{
    use SyncsUsersWithNotification;

    public function __construct(
        private readonly ChecklistScheduleGeneratorService $generatorService,
        private readonly EquipmentCascadeSoftDeleteService $cascadeService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable
     */
    public function execute(string $id, array $data): array
    {
        $session = DB::transaction(function () use ($id, $data) {
            $session = ChecklistSession::findOrFail($id);

            return $this->update($session, $data);
        });

        $sessionResponse = $session->toArray();
        if (isset($data['session_date'])) {
            $sessionResponse['session_date'] = $data['session_date'];
        } else {
            $latestSchedule = ChecklistSchedule::where('checklist_session_id', $session->id)->latest('date')->first();
            $latestDate = $latestSchedule?->date;
            $sessionResponse['session_date'] = $latestDate
                ? CarbonImmutable::parse($latestDate)->toDateString()
                : null;
        }
        $sessionResponse['users'] = $session->users;

        return $sessionResponse;
    }

    /**
     * Update a checklist template and its generated schedules.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function update(ChecklistSession $session, array $data): ChecklistSession
    {
        $sessionFields = array_intersect_key($data, array_flip([
            'name',
            'equipment_id',
            'session_date',
            'cycle_type',
            'cycle_interval',
            'schedule_mode',
        ]));

        $session->fill($sessionFields);
        $sessionDateChanged = $session->isDirty('session_date');

        if ($session->isDirty()) {
            $session->save();
        }

        if (array_key_exists('user_ids', $data)) {
            $this->syncUsersAndNotify(
                $session->users(),
                $data['user_ids'] ?? [],
                'checklist_session',
                $session->id,
                $session->name
            );
        }

        if (array_key_exists('details', $data) && is_array($data['details'])) {
            $inputDetails = $data['details'];
            $existingDetails = $session->details;
            $keepDetailIds = [];

            foreach ($inputDetails as $detailData) {
                if (empty($detailData['checklist_id'])) {
                    continue;
                }

                $detail = null;
                if (! empty($detailData['id'])) {
                    $detail = $existingDetails->firstWhere('id', $detailData['id']);
                }
                if (! $detail) {
                    $detail = $existingDetails->firstWhere('checklist_id', $detailData['checklist_id']);
                }

                if ($detail) {
                    $detail->update([
                        'description' => $detailData['description'] ?? $detail->description,
                    ]);
                } else {
                    $detail = $session->details()->create([
                        'checklist_id' => $detailData['checklist_id'],
                        'description' => $detailData['description'] ?? null,
                    ]);
                }
                $keepDetailIds[] = $detail->id;
            }

            $toRemove = $existingDetails->reject(fn ($d) => in_array($d->id, $keepDetailIds, true));
            foreach ($toRemove as $removedDetail) {
                $schedulesToDelete = $removedDetail->schedules;
                $this->cascadeService->deleteChecklistSchedules($schedulesToDelete);
                $removedDetail->delete();
            }
            $session->load('details');
        }

        $isRepeating = ($session->schedule_mode ?? 'repeating') === 'repeating';
        if ($session->session_date) {
            if ($isRepeating) {
                $this->applyScheduleUpdates($session, $data['schedules'] ?? []);

                $sessionDate = CarbonImmutable::parse($session->session_date);
                $startDate = $sessionDate;
                $endDate = $sessionDate->addDays(30);

                $this->generatorService->regenerateForSession(
                    $session,
                    $session->equipment_id,
                    $startDate,
                    $endDate,
                    $session->cycle_type ?? 'daily',
                    (int) ($session->cycle_interval ?? 1)
                );
            } else {
                $targetDate = CarbonImmutable::parse($session->session_date);
                $this->generatorService->regenerateSingleForSession(
                    $session,
                    $session->equipment_id,
                    $targetDate
                );
            }
        }

        return $session->fresh()->load([
            'equipment',
            'details.schedules.logs',
            'schedules.users',
            'users',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $schedules
     */
    private function applyScheduleUpdates(ChecklistSession $session, array $schedules): void
    {
        foreach ($schedules as $scheduleData) {
            if (empty($scheduleData['id'])) {
                continue;
            }

            $schedule = $session->schedules()->find($scheduleData['id']);
            if (! $schedule) {
                continue;
            }

            if (! empty($scheduleData['date']) && $schedule->date !== $scheduleData['date']) {
                $schedule->update([
                    'date' => $scheduleData['date'],
                    'original_date' => $schedule->original_date ?? $schedule->date,
                    'is_rescheduled' => true,
                ]);
            }

            $this->syncScheduleUsers($session, $schedule, $scheduleData);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $schedules
     */
    private function syncManualSchedules(ChecklistSession $session, array $schedules): void
    {
        $keepIds = collect($schedules)->pluck('id')->filter()->values()->all();
        $protectedIds = $this->getProtectedScheduleIds($session);

        $schedules = $session->schedules()
            ->whereNotIn('id', $keepIds)
            ->whereNotIn('id', $protectedIds)
            ->get();

        $this->cascadeService->deleteChecklistSchedules($schedules);

        foreach ($schedules as $scheduleData) {
            if (! empty($scheduleData['id'])) {
                $schedule = $session->schedules()->find($scheduleData['id']);
                if (! $schedule) {
                    continue;
                }

                $updateData = array_filter([
                    'date' => $scheduleData['date'] ?? null,
                    'checklist_detail_id' => $scheduleData['checklist_detail_id'] ?? null,
                ], static fn (mixed $value): bool => $value !== null);

                if (isset($updateData['date']) && $schedule->date !== $updateData['date']) {
                    $updateData['original_date'] = $schedule->original_date ?? $schedule->date;
                    $updateData['is_rescheduled'] = true;
                }

                if (! empty($updateData)) {
                    $schedule->update($updateData);
                }

                $this->syncScheduleUsers($session, $schedule, $scheduleData);

                continue;
            }

            if (empty($scheduleData['checklist_detail_id']) || empty($scheduleData['date'])) {
                continue;
            }

            $detail = $session->details()->find($scheduleData['checklist_detail_id']);
            if (! $detail) {
                continue;
            }

            $schedule = ChecklistSchedule::create([
                'equipment_id' => $session->equipment_id,
                'checklist_session_id' => $session->id,
                'checklist_detail_id' => $detail->id,
                'date' => $scheduleData['date'],
                'original_date' => $scheduleData['date'],
                'is_rescheduled' => false,
            ]);
            $schedule->logs()->create([
                'status' => 'pending',
                'result' => null,
            ]);

            $this->syncScheduleUsers($session, $schedule, $scheduleData);
        }
    }

    private function moveUnprotectedOneTimeSchedules(ChecklistSession $session, CarbonImmutable $date): void
    {
        $session->schedules()
            ->whereNotIn('id', $this->getProtectedScheduleIds($session))
            ->update([
                'date' => $date->toDateString(),
                'original_date' => $date->toDateString(),
                'is_rescheduled' => false,
            ]);
    }

    /**
     * @param  array<string, mixed>  $scheduleData
     */
    private function syncScheduleUsers(
        ChecklistSession $session,
        ChecklistSchedule $schedule,
        array $scheduleData
    ): void {
        if (! array_key_exists('user_ids', $scheduleData)) {
            return;
        }

        $this->syncUsersAndNotify(
            $schedule->users(),
            $scheduleData['user_ids'] ?? [],
            'checklist_session',
            $schedule->id,
            "{$session->name} ({$schedule->date})"
        );
    }

    /**
     * @return string[]
     */
    private function getProtectedScheduleIds(ChecklistSession $session): array
    {
        $scheduleIds = $session->schedules()->pluck('id');

        $completedIds = ChecklistLog::whereIn('checklist_schedule_id', $scheduleIds)
            ->where('status', 'completed')
            ->pluck('checklist_schedule_id')
            ->all();

        $rescheduledIds = $session->schedules()
            ->where('is_rescheduled', true)
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($completedIds, $rescheduledIds)));
    }
}
