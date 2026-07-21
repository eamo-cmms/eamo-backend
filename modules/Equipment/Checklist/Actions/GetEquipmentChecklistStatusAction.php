<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetEquipmentChecklistStatusAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $endDateString = $request->input('end_date') ?? Carbon::today()->toDateString();
        $startDateString = $request->input('start_date') ?? Carbon::parse($endDateString)->subDays(6)->toDateString();
        $startDate = Carbon::parse($startDateString);
        $endDate = Carbon::parse($endDateString);

        $schedulesByDate = ChecklistScheduleQuery::make()
            ->dateRange($startDate->toDateString(), $endDate->toDateString())
            ->withLogs()
            ->get()
            ->groupBy(fn (ChecklistSchedule $schedule): string => Carbon::parse($schedule->date)->toDateString());

        $dailyStats = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateString = $date->toDateString();
            $stats = $this->summarizeSchedules($schedulesByDate->get($dateString, collect()));

            $dailyStats[] = [
                'date' => $dateString,
                'total_checklists' => $stats['total'],
                'passed' => $stats['passed'],
                'failed' => $stats['failed'],
                'pending' => $stats['pending'],
                'completion_rate' => $stats['total'] > 0
                    ? (int) round(($stats['completed'] / $stats['total']) * 100)
                    : 0,
            ];
        }

        $todaySchedules = $schedulesByDate->get($endDate->toDateString(), collect());
        $todayStats = $this->summarizeSchedules($todaySchedules);
        $schedulesByEquipment = $todaySchedules->groupBy('equipment_id');
        $equipments = Equipment::query()->where('is_active', true)->get();

        $detailedEquipments = $equipments->map(function (Equipment $equipment) use ($schedulesByEquipment): array {
            $schedules = $schedulesByEquipment->get($equipment->id, collect());
            $stats = $this->summarizeSchedules($schedules);
            $firstSchedule = $schedules->first();

            if ($stats['total'] === 0) {
                $status = 'pending';
                $reason = 'No checklist schedule for this date';
            } elseif ($stats['pending'] > 0) {
                $status = 'pending';
                $reason = 'Some checklist items are not completed';
            } elseif ($stats['failed'] > 0) {
                $status = 'failed';
                $reason = 'Some checklist items failed';
            } else {
                $status = 'passed';
                $reason = 'All checklist items passed';
            }

            return [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'code' => $equipment->code,
                'status' => $status,
                'reason' => $reason,
                'session_id' => $firstSchedule?->checklist_session_id,
                'total_details' => $stats['total'],
                'logged_details' => $stats['completed'],
                'completion_rate' => $stats['total'] > 0
                    ? (int) round(($stats['completed'] / $stats['total']) * 100)
                    : 0,
            ];
        })->values();

        return response()->json([
            'start_date' => $startDateString,
            'end_date' => $endDateString,
            'total_active_equipments' => $equipments->count(),
            'total_equipments' => $todayStats['total'],
            'daily_stats' => $dailyStats,
            'today' => [
                'date' => $endDate->toDateString(),
                'total_checklists' => $todayStats['total'],
                'passed' => $todayStats['passed'],
                'failed' => $todayStats['failed'],
                'pending' => $todayStats['pending'],
                'equipments' => $detailedEquipments,
            ],
        ]);
    }

    /**
     * @param  Collection<int, ChecklistSchedule>  $schedules
     * @return array{total: int, completed: int, passed: int, failed: int, pending: int}
     */
    private function summarizeSchedules(Collection $schedules): array
    {
        $completed = 0;
        $passed = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            $log = $schedule->logs
                ->where('status', 'completed')
                ->sortBy('checked_at')
                ->last();

            if (! $log instanceof ChecklistLog) {
                continue;
            }

            $completed++;
            if ($log->result === 'pass') {
                $passed++;
            }

            if ($log->result === 'fail') {
                $failed++;
            }
        }

        $total = $schedules->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'passed' => $passed,
            'failed' => $failed,
            'pending' => $total - $completed,
        ];
    }
}
