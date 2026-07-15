<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
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

        // Fetch all active sessions that could be active in the range (session_date >= startDate)
        $allSessions = ChecklistSession::query()
            ->where('session_date', '>=', $startDate->startOfDay()->toDateTimeString())
            ->with(['details.logs'])
            ->get();

        $period = CarbonPeriod::create($startDate, $endDate);
        $dailyStats = [];

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $dateStart = $date->copy()->startOfDay();
            $dateEnd = $date->copy()->endOfDay();

            // Active sessions on this specific date are those where session_date >= date
            $activeSessionsForDay = $allSessions->filter(function ($session) use ($dateStr) {
                return Carbon::parse($session->session_date)->toDateString() >= $dateStr;
            });

            $totalDetailsForDay = 0;
            $passedForDay = 0;
            $failedForDay = 0;

            foreach ($activeSessionsForDay as $session) {
                foreach ($session->details as $detail) {
                    $totalDetailsForDay++;

                    // Find logs for this detail created on this specific day
                    $dayLogs = $detail->logs->filter(function ($log) use ($dateStart, $dateEnd) {
                        return $log->created_at >= $dateStart && $log->created_at <= $dateEnd;
                    });

                    if (! $dayLogs->isEmpty()) {
                        $latestLog = $dayLogs->sortBy('created_at')->last();
                        if ($latestLog && $latestLog->result === 'pass') {
                            $passedForDay++;
                        } elseif ($latestLog && $latestLog->result === 'fail') {
                            $failedForDay++;
                        }
                    }
                }
            }

            $pendingForDay = $totalDetailsForDay - ($passedForDay + $failedForDay);
            $completionRate = $totalDetailsForDay > 0 ? (int) round(($passedForDay / $totalDetailsForDay) * 100) : 0;

            $dailyStats[] = [
                'date' => $dateStr,
                'total_checklists' => $totalDetailsForDay,
                'passed' => $passedForDay,
                'failed' => $failedForDay,
                'pending' => $pendingForDay,
                'completion_rate' => $completionRate,
            ];
        }

        // Today's details
        $todayStr = $endDate->toDateString();
        $todayStart = $endDate->copy()->startOfDay();
        $todayEnd = $endDate->copy()->endOfDay();

        $activeSessionsToday = $allSessions->filter(function ($session) use ($todayStr) {
            return Carbon::parse($session->session_date)->toDateString() >= $todayStr;
        });
        $activeSessionsByEquipment = $activeSessionsToday->keyBy('equipment_id');

        $todayTotalDetails = 0;
        $todayPassed = 0;
        $todayFailed = 0;

        foreach ($activeSessionsToday as $session) {
            foreach ($session->details as $detail) {
                $todayTotalDetails++;

                $dayLogs = $detail->logs->filter(function ($log) use ($todayStart, $todayEnd) {
                    return $log->created_at >= $todayStart && $log->created_at <= $todayEnd;
                });

                if (! $dayLogs->isEmpty()) {
                    $latestLog = $dayLogs->sortBy('created_at')->last();
                    if ($latestLog && $latestLog->result === 'pass') {
                        $todayPassed++;
                    } elseif ($latestLog && $latestLog->result === 'fail') {
                        $todayFailed++;
                    }
                }
            }
        }
        $todayPending = $todayTotalDetails - ($todayPassed + $todayFailed);

        // Fetch all active equipments for the detailed listing
        $equipments = Equipment::query()->where('is_active', true)->get();
        $detailedEquipments = [];

        foreach ($equipments as $equipment) {
            $session = $activeSessionsByEquipment->get($equipment->id);

            $status = 'pending';
            $reason = 'No active session (session_date >= today)';
            $totalDetails = 0;
            $loggedDetails = 0;

            if ($session) {
                $details = $session->details ?? collect();
                $totalDetails = $details->count();
                $sessionPassed = 0;
                $sessionFailed = 0;

                foreach ($details as $detail) {
                    $dayLogs = $detail->logs->filter(function ($log) use ($todayStart, $todayEnd) {
                        return $log->created_at >= $todayStart && $log->created_at <= $todayEnd;
                    });

                    if (! $dayLogs->isEmpty()) {
                        $loggedDetails++;
                        $latestLog = $dayLogs->sortBy('created_at')->last();
                        if ($latestLog && $latestLog->result === 'pass') {
                            $sessionPassed++;
                        } else {
                            $sessionFailed++;
                        }
                    }
                }

                if ($loggedDetails < $totalDetails) {
                    $status = 'pending';
                    $reason = 'Some checklist items are missing logs';
                } else {
                    if ($sessionFailed > 0) {
                        $status = 'failed';
                        $reason = 'Some checklist items failed';
                    } else {
                        $status = 'passed';
                        $reason = 'All checklist items passed';
                    }
                }
            }

            $detailedEquipments[] = [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'code' => $equipment->code,
                'status' => $status,
                'reason' => $reason,
                'session_id' => $session?->id ?? null,
                'total_details' => $totalDetails,
                'logged_details' => $loggedDetails,
                'completion_rate' => $totalDetails > 0 ? (int) round(($loggedDetails / $totalDetails) * 100) : 0,
            ];
        }

        return response()->json([
            'start_date' => $startDateString,
            'end_date' => $endDateString,
            'total_active_equipments' => $equipments->count(),
            'total_equipments' => $todayTotalDetails,
            'daily_stats' => $dailyStats,
            'today' => [
                'date' => $todayStr,
                'total_checklists' => $todayTotalDetails,
                'passed' => $todayPassed,
                'failed' => $todayFailed,
                'pending' => $todayPending,
                'equipments' => $detailedEquipments,
            ],
        ]);
    }
}
