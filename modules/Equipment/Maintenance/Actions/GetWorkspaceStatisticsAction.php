<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

final class GetWorkspaceStatisticsAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        // 1. Maintenance Statistics
        $totalMaintenance = MaintenanceSchedule::count();
        $completedMaintenance = MaintenanceSchedule::whereHas('maintenanceLogs', function ($q): void {
            $q->where('result', 'Completed');
        })->count();

        $pendingMaintenance = $totalMaintenance - $completedMaintenance;
        $overdueMaintenance = MaintenanceSchedule::where('date', '<', $today)
            ->whereDoesntHave('maintenanceLogs', function ($q): void {
                $q->where('result', 'Completed');
            })->count();
        $upcomingMaintenance = max(0, $pendingMaintenance - $overdueMaintenance);
        $maintenanceRate = $totalMaintenance > 0
            ? round(($completedMaintenance / $totalMaintenance) * 100, 1)
            : 0;

        // 2. Checklist Statistics
        $totalChecklist = ChecklistSchedule::count();
        $completedChecklist = ChecklistSchedule::whereHas('logs', function ($q): void {
            $q->where('status', 'completed')->where('result', 'pass');
        })->count();
        $failedChecklist = ChecklistSchedule::whereHas('logs', function ($q): void {
            $q->where('status', 'completed')->where('result', 'fail');
        })->count();

        $pendingChecklist = ChecklistSchedule::whereDoesntHave('logs', function ($q): void {
            $q->where('status', 'completed');
        })->count();
        $overdueChecklist = ChecklistSchedule::where('date', '<', $today)
            ->whereDoesntHave('logs', function ($q): void {
                $q->where('status', 'completed');
            })->count();
        $upcomingChecklist = max(0, $pendingChecklist - $overdueChecklist);
        $checklistRate = $totalChecklist > 0
            ? round(($completedChecklist / $totalChecklist) * 100, 1)
            : 0;

        // 3. Top Performer Users
        // Maintenance completions by user from eamo_maintenance_logs (direct user_id)
        $maintenanceScores = DB::table('eamo_maintenance_logs')
            ->whereNotNull('user_id')
            ->where('result', 'Completed')
            ->whereNull('deleted_at')
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(id) as count'))
            ->pluck('count', 'user_id')
            ->toArray();

        // Checklist completions by user from eamo_checklist_log_users pivot table
        $checklistScores = DB::table('eamo_checklist_log_users')
            ->join('eamo_checklist_logs', 'eamo_checklist_logs.id', '=', 'eamo_checklist_log_users.checklist_log_id')
            ->where('eamo_checklist_logs.status', 'completed')
            ->whereNull('eamo_checklist_logs.deleted_at')
            ->whereNull('eamo_checklist_log_users.deleted_at')
            ->groupBy('eamo_checklist_log_users.user_id')
            ->select('eamo_checklist_log_users.user_id', DB::raw('COUNT(DISTINCT eamo_checklist_logs.id) as count'))
            ->pluck('count', 'user_id')
            ->toArray();

        // All user IDs with any completion
        $allUserIds = array_unique(array_merge(array_keys($maintenanceScores), array_keys($checklistScores)));

        $users = User::whereIn('id', $allUserIds)->get()->keyBy('id');

        $leaderboard = [];
        foreach ($allUserIds as $userId) {
            $user = $users->get($userId);
            if (! $user) {
                continue;
            }
            $mCount = (int) ($maintenanceScores[$userId] ?? 0);
            $cCount = (int) ($checklistScores[$userId] ?? 0);
            $totalCount = $mCount + $cCount;

            if ($totalCount > 0) {
                $leaderboard[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'maintenance_completed' => $mCount,
                    'checklist_completed' => $cCount,
                    'total_completed' => $totalCount,
                ];
            }
        }

        // Sort descending by total_completed
        usort($leaderboard, static fn (array $a, array $b): int => $b['total_completed'] <=> $a['total_completed']);
        $topUsers = array_slice($leaderboard, 0, 5);

        return response()->json([
            'status' => 'success',
            'data' => [
                'maintenance' => [
                    'total' => $totalMaintenance,
                    'completed' => $completedMaintenance,
                    'pending' => $pendingMaintenance,
                    'overdue' => $overdueMaintenance,
                    'upcoming' => $upcomingMaintenance,
                    'completion_rate' => $maintenanceRate,
                ],
                'checklist' => [
                    'total' => $totalChecklist,
                    'completed' => $completedChecklist,
                    'failed' => $failedChecklist,
                    'pending' => $pendingChecklist,
                    'overdue' => $overdueChecklist,
                    'upcoming' => $upcomingChecklist,
                    'completion_rate' => $checklistRate,
                ],
                'top_users' => $topUsers,
            ],
        ]);
    }
}
