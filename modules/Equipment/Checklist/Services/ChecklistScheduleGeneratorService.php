<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Concerns\SyncsUsersWithNotification;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class ChecklistScheduleGeneratorService
{
    use SyncsUsersWithNotification;

    public const MAX_SCHEDULES = 100;

    private readonly EquipmentCascadeSoftDeleteService $cascadeService;

    public function __construct(?EquipmentCascadeSoftDeleteService $cascadeService = null)
    {
        $this->cascadeService = $cascadeService ?? app(EquipmentCascadeSoftDeleteService::class);
    }

    /**
     * Generate dates based on start date, end date, cycle type, and cycle interval.
     *
     * @return CarbonImmutable[]
     */
    public function generateDates(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        string $cycleType,
        int $cycleInterval
    ): array {
        $dates = [];
        $currentDate = $startDate;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $dates[] = $currentDate;
            $currentDate = match ($cycleType) {
                'daily' => $currentDate->addDays($cycleInterval),
                'weekly' => $currentDate->addWeeks($cycleInterval),
                'monthly' => $currentDate->addMonths($cycleInterval),
                'yearly' => $currentDate->addYears($cycleInterval),
                default => throw new \InvalidArgumentException("Invalid cycle type: {$cycleType}"),
            };
        }

        return $dates;
    }

    /**
     * Generate schedules for a checklist session template (first-time creation).
     *
     * @throws ValidationException
     */
    public function generateForSession(
        ChecklistSession $session,
        string $equipmentId,
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        string $cycleType,
        int $cycleInterval
    ): void {
        $details = $session->details;
        if ($details->isEmpty()) {
            return;
        }

        $dates = $this->generateDates($startDate, $endDate, $cycleType, $cycleInterval);
        $detailsCount = max(1, $details->count());
        $totalSchedules = count($dates) * $detailsCount;

        if ($totalSchedules > self::MAX_SCHEDULES) {
            $maxDates = (int) floor(self::MAX_SCHEDULES / $detailsCount);
            $dates = array_slice($dates, 0, max(1, $maxDates));
        }

        $userIds = $session->users->pluck('id')->toArray();

        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');
            foreach ($details as $detail) {
                $schedule = ChecklistSchedule::create([
                    'equipment_id' => $equipmentId,
                    'checklist_session_id' => $session->id,
                    'checklist_detail_id' => $detail->id,
                    'date' => $formattedDate,
                    'original_date' => $formattedDate,
                    'is_rescheduled' => false,
                ]);

                $this->createPendingLog($schedule);

                if (! empty($userIds)) {
                    $schedule->users()->sync($userIds);
                    // Optionally notify user
                    $label = ($session->name ?? 'Checklist session')." ($formattedDate)";
                    $this->syncUsersAndNotify(
                        $schedule->users(),
                        $userIds,
                        'checklist_session',
                        $schedule->id,
                        $label
                    );
                }
            }
        }
    }

    /**
     * Regenerate schedules for a session, preserving protected ones.
     *
     * @throws ValidationException
     */
    public function regenerateForSession(
        ChecklistSession $session,
        string $equipmentId,
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        string $cycleType,
        int $cycleInterval
    ): void {
        // 1. Collect protected schedule IDs
        $protectedIds = $this->getProtectedScheduleIds($session, $equipmentId, $startDate, $endDate);

        // 2. Delete unprotected schedules in range
        $schedules = $session->schedules()
            ->where('equipment_id', $equipmentId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotIn('id', $protectedIds)
            ->get();

        $this->cascadeService->deleteChecklistSchedules($schedules);

        // 3. Determine items/details in this template session
        $details = $session->details;
        if ($details->isEmpty()) {
            return;
        }

        // 4. Generate target dates
        $dates = $this->generateDates($startDate, $endDate, $cycleType, $cycleInterval);
        $detailsCount = max(1, $details->count());
        $totalNewSchedules = count($dates) * $detailsCount;

        if ($totalNewSchedules > self::MAX_SCHEDULES) {
            $maxDates = (int) floor(self::MAX_SCHEDULES / $detailsCount);
            $dates = array_slice($dates, 0, max(1, $maxDates));
        }

        $userIds = $session->users->pluck('id')->toArray();

        // 6. Recreate missing schedules
        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');
            foreach ($details as $detail) {
                $exists = $session->schedules()
                    ->where('equipment_id', $equipmentId)
                    ->where('checklist_detail_id', $detail->id)
                    ->where(function ($query) use ($formattedDate) {
                        $query->where('date', $formattedDate)
                            ->orWhere('original_date', $formattedDate);
                    })
                    ->exists();

                if (! $exists) {
                    $schedule = ChecklistSchedule::create([
                        'equipment_id' => $equipmentId,
                        'checklist_session_id' => $session->id,
                        'checklist_detail_id' => $detail->id,
                        'date' => $formattedDate,
                        'original_date' => $formattedDate,
                        'is_rescheduled' => false,
                    ]);

                    $this->createPendingLog($schedule);

                    if (! empty($userIds)) {
                        $schedule->users()->sync($userIds);
                        $label = ($session->name ?? 'Checklist session')." ($formattedDate)";
                        $this->syncUsersAndNotify(
                            $schedule->users(),
                            $userIds,
                            'checklist_session',
                            $schedule->id,
                            $label
                        );
                    }
                }
            }
        }
    }

    /**
     * Get IDs of schedules that should not be deleted.
     *
     * @return string[]
     */
    private function getProtectedScheduleIds(
        ChecklistSession $session,
        string $equipmentId,
        CarbonImmutable $startDate,
        CarbonImmutable $endDate
    ): array {
        $scheduleIds = $session->schedules()
            ->where('equipment_id', $equipmentId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('id');

        // Only completed schedules represent work that must be preserved.
        $loggedIds = ChecklistLog::whereIn('checklist_schedule_id', $scheduleIds)
            ->where('status', 'completed')
            ->pluck('checklist_schedule_id')
            ->toArray();

        // Schedules manually rescheduled
        $rescheduledIds = $session->schedules()
            ->where('equipment_id', $equipmentId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_rescheduled', true)
            ->pluck('id')
            ->toArray();

        return array_values(array_unique(array_merge($loggedIds, $rescheduledIds)));
    }

    /**
     * Regenerate schedules for a single-mode checklist session.
     * Keeps schedules ONLY on $targetDate, and deletes all unprotected schedules on any other dates.
     */
    public function regenerateSingleForSession(
        ChecklistSession $session,
        string $equipmentId,
        CarbonImmutable $targetDate
    ): void {
        $targetDateStr = $targetDate->format('Y-m-d');

        // Collect protected schedule IDs across all dates
        $scheduleIds = $session->schedules()->pluck('id');
        $loggedIds = ChecklistLog::whereIn('checklist_schedule_id', $scheduleIds)
            ->where('status', 'completed')
            ->pluck('checklist_schedule_id')
            ->toArray();
        $rescheduledIds = $session->schedules()
            ->where('is_rescheduled', true)
            ->pluck('id')
            ->toArray();
        $protectedIds = array_values(array_unique(array_merge($loggedIds, $rescheduledIds)));

        // Delete all unprotected schedules for this session on dates != targetDateStr
        $unprotectedSchedulesOnOtherDates = $session->schedules()
            ->where('equipment_id', $equipmentId)
            ->where('date', '!=', $targetDateStr)
            ->whereNotIn('id', $protectedIds)
            ->get();

        $this->cascadeService->deleteChecklistSchedules($unprotectedSchedulesOnOtherDates);

        // Ensure schedules exist on targetDateStr for each detail
        $details = $session->details;
        if ($details->isEmpty()) {
            return;
        }

        $userIds = $session->users->pluck('id')->toArray();

        foreach ($details as $detail) {
            $exists = $session->schedules()
                ->where('equipment_id', $equipmentId)
                ->where('checklist_detail_id', $detail->id)
                ->where(function ($query) use ($targetDateStr) {
                    $query->where('date', $targetDateStr)
                        ->orWhere('original_date', $targetDateStr);
                })
                ->exists();

            if (! $exists) {
                $schedule = ChecklistSchedule::create([
                    'equipment_id' => $equipmentId,
                    'checklist_session_id' => $session->id,
                    'checklist_detail_id' => $detail->id,
                    'date' => $targetDateStr,
                    'original_date' => $targetDateStr,
                    'is_rescheduled' => false,
                ]);

                $this->createPendingLog($schedule);

                if (! empty($userIds)) {
                    $schedule->users()->sync($userIds);
                    $label = ($session->name ?? 'Checklist session')." ($targetDateStr)";
                    $this->syncUsersAndNotify(
                        $schedule->users(),
                        $userIds,
                        'checklist_session',
                        $schedule->id,
                        $label
                    );
                }
            }
        }
    }

    private function createPendingLog(ChecklistSchedule $schedule): void
    {
        $schedule->logs()->create([
            'status' => 'pending',
            'result' => null,
        ]);
    }
}
