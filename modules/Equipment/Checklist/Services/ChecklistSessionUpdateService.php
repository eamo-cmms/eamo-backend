<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Concerns\SyncsUsersWithNotification;
use Carbon\CarbonImmutable;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Throwable;

final class ChecklistSessionUpdateService
{
    use SyncsUsersWithNotification;

    public function __construct(
        private readonly ChecklistScheduleGeneratorService $generatorService
    ) {}

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

        $isRepeating = ! empty($session->cycle_type) && ! empty($session->cycle_interval);
        if ($isRepeating && $session->session_date) {
            $this->applyScheduleUpdates($session, $data['schedules'] ?? []);

            $this->generatorService->regenerateForSession(
                $session,
                $session->equipment_id,
                CarbonImmutable::today(),
                CarbonImmutable::parse($session->session_date),
                $session->cycle_type,
                (int) $session->cycle_interval
            );
        } elseif (array_key_exists('schedules', $data)) {
            $this->syncManualSchedules($session, $data['schedules'] ?? []);
        } elseif ($sessionDateChanged && $session->session_date) {
            $this->moveUnprotectedOneTimeSchedules($session, CarbonImmutable::parse($session->session_date));
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

        $session->schedules()
            ->whereNotIn('id', $keepIds)
            ->whereNotIn('id', $protectedIds)
            ->delete();

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
