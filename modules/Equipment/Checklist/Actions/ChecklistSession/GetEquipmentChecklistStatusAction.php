<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;
use Modules\Equipment\Checklist\Requests\GetEquipmentChecklistStatusRequest;

final class GetEquipmentChecklistStatusAction
{
    use AsAction;

    public function asController(GetEquipmentChecklistStatusRequest $request): JsonResponse
    {
        $data = $request->validated();

        $endDateString = $data['end_date'] ?? Carbon::today()->toDateString();
        $startDateString = $data['start_date'] ?? Carbon::parse($endDateString)->subDays(6)->toDateString();
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

        return response()->json([
            'start_date' => $startDateString,
            'end_date' => $endDateString,
            'total_active_equipments' => 0,
            'total_equipments' => $todayStats['total'],
            'daily_stats' => $dailyStats,
            'today' => [
                'date' => $endDate->toDateString(),
                'total_checklists' => $todayStats['total'],
                'passed' => $todayStats['passed'],
                'failed' => $todayStats['failed'],
                'pending' => $todayStats['pending'],
                'equipments' => [],
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
