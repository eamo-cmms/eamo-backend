<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Concerns\SyncsUsersWithNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Throwable;

final class StoreChecklistSessionService
{
    use SyncsUsersWithNotification;

    public function __construct(
        private readonly ChecklistScheduleGeneratorService $scheduleGeneratorService
    ) {}

    /**
     * Store a checklist session, its details, schedules and pending checklist logs.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $userIds
     * @return array<string, mixed>
     *
     * @throws Throwable
     */
    public function execute(array $data, array $userIds): array
    {
        $session = DB::transaction(function () use ($data, $userIds): ChecklistSession {
            $session = ChecklistSession::firstOrCreate([
                'equipment_id' => $data['equipment_id'],
            ], [
                'name' => $data['name'],
            ]);

            $session->update([
                'name' => $data['name'],
                'session_date' => $data['session_date'],
                'cycle_type' => $data['cycle_type'] ?? null,
                'cycle_interval' => $data['cycle_interval'] ?? null,
            ]);

            if (! empty($userIds)) {
                $this->syncUsersAndNotify(
                    $session->users(),
                    $userIds,
                    'checklist_session',
                    $session->id,
                    $session->name
                );
            }

            $this->storeDetails($session, $data['details'] ?? []);

            if ($session->session_date) {
                $startDate = $this->resolveStartDate($data);
                $endDate = CarbonImmutable::parse($session->session_date);

                $this->scheduleGeneratorService->regenerateForSession(
                    $session,
                    $session->equipment_id,
                    $startDate,
                    $endDate,
                    $session->cycle_type ?? 'daily',
                    (int) ($session->cycle_interval ?? 1)
                );

            }

            return $session;
        });

        return $this->buildResponse($session, $data['session_date']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     */
    private function storeDetails(ChecklistSession $session, array $details): void
    {
        foreach ($details as $detailData) {
            $session->details()->firstOrCreate([
                'checklist_id' => $detailData['checklist_id'],
            ], [
                'description' => $detailData['description'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveStartDate(array $data): CarbonImmutable
    {
        $isRepeating = ! empty($data['cycle_type']) && ! empty($data['cycle_interval']);

        return $isRepeating
            ? CarbonImmutable::today()
            : CarbonImmutable::parse($data['session_date']);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResponse(ChecklistSession $session, string $sessionDate): array
    {
        $schedules = ChecklistSchedule::with(['checklistDetail', 'logs.users', 'users'])
            ->where('checklist_session_id', $session->id)
            ->whereDate('date', $sessionDate)
            ->get();

        return [
            'id' => $session->id,
            'name' => $session->name,
            'equipment_id' => $session->equipment_id,
            'session_date' => $sessionDate,
            'details' => $schedules->map(static function (ChecklistSchedule $schedule): array {
                return [
                    'id' => $schedule->checklist_detail_id,
                    'session_id' => $schedule->checklist_session_id,
                    'checklist_id' => $schedule->checklistDetail?->checklist_id,
                    'description' => $schedule->checklistDetail?->description,
                    'logs' => $schedule->logs,
                ];
            })->toArray(),
            'users' => $session->users,
            'created_at' => $session->created_at
                ? CarbonImmutable::parse($session->created_at)->toDateTimeString()
                : null,
            'updated_at' => $session->updated_at
                ? CarbonImmutable::parse($session->updated_at)->toDateTimeString()
                : null,
        ];
    }
}
