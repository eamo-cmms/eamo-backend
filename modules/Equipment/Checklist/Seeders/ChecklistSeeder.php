<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Services\ChecklistScheduleGeneratorService;
use Modules\Masterdata\Equipment\Models\Equipment;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing checklist tables in correct dependency order
        DB::table('eamo_checklist_log_users')->delete();
        ChecklistLog::query()->delete();
        DB::table('eamo_checklist_schedule_user')->delete();
        ChecklistSchedule::query()->delete();
        ChecklistDetail::query()->delete();
        DB::table('eamo_checklist_session_users')->delete();
        ChecklistSession::query()->delete();

        $equipments = Equipment::take(5)->get();
        if ($equipments->isEmpty()) {
            return;
        }

        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $scheduleGenerator = app(ChecklistScheduleGeneratorService::class);
        $startDate = CarbonImmutable::today()->subDays(5);
        $endDate = CarbonImmutable::today()->addDays(1);

        $checklistTemplates = [
            [
                'name' => 'Kiểm tra định kỳ hàng ngày',
                'cycle_type' => 'daily',
                'cycle_interval' => 1,
                'details' => [
                    'Kiểm tra rò rỉ dầu và các khớp nối thủy lực',
                    'Kiểm tra và ghi nhận nhiệt độ hoạt động động cơ',
                    'Vệ sinh tấm lọc bụi và làm sạch bề mặt thiết bị',
                    'Kiểm tra độ căng của băng tải / xích truyền động',
                ],
            ],
            [
                'name' => 'Kiểm tra an toàn thiết bị',
                'cycle_type' => 'daily',
                'cycle_interval' => 1,
                'details' => [
                    'Kiểm tra nút dừng khẩn cấp (Emergency Stop)',
                    'Kiểm tra hoạt động của cảm biến an toàn và rào chắn',
                    'Kiểm tra áp suất khí nén đầu vào',
                ],
            ],
        ];

        foreach ($equipments as $index => $equipment) {
            // Assign a template based on index
            $template = $checklistTemplates[$index % count($checklistTemplates)];

            // Create checklist session
            $session = ChecklistSession::create([
                'id' => (string) Str::uuid(),
                'name' => $template['name'].' - '.$equipment->name,
                'equipment_id' => $equipment->id,
                'session_date' => $endDate,
                'cycle_type' => $template['cycle_type'],
                'cycle_interval' => $template['cycle_interval'],
                'schedule_mode' => 'repeating',
            ]);

            // Sync random users to the session (assign 1-3 users)
            $sessionUsers = $users->random(min(3, $users->count()))->pluck('id')->toArray();
            $session->users()->sync($sessionUsers);

            // Create details
            $details = [];
            foreach ($template['details'] as $detailDesc) {
                $details[] = ChecklistDetail::create([
                    'id' => (string) Str::uuid(),
                    'checklist_id' => (string) Str::uuid(),
                    'session_id' => $session->id,
                    'description' => $detailDesc,
                ]);
            }

            // Load details relationship so generator service sees it
            $session->setRelation('details', collect($details));
            $session->setRelation('users', collect($users->whereIn('id', $sessionUsers)));

            // Generate schedules and pending logs
            $scheduleGenerator->generateForSession(
                $session,
                $equipment->id,
                $startDate,
                $endDate,
                $template['cycle_type'],
                $template['cycle_interval']
            );

            // Now, complete some of the generated schedules/logs
            $schedules = ChecklistSchedule::where('checklist_session_id', $session->id)->get();

            foreach ($schedules as $schedule) {
                $scheduleDate = CarbonImmutable::parse($schedule->date);

                // If the schedule is in the past (before today), mark the log as completed
                if ($scheduleDate->isBefore(CarbonImmutable::today())) {
                    $log = ChecklistLog::where('checklist_schedule_id', $schedule->id)->first();
                    if ($log) {
                        // 90% pass, 10% fail
                        $result = (rand(1, 10) <= 9) ? 'pass' : 'fail';
                        $checkedAt = $scheduleDate->setTime(rand(8, 17), rand(0, 59), rand(0, 59));

                        $log->update([
                            'status' => 'completed',
                            'result' => $result,
                            'checked_at' => $checkedAt,
                        ]);

                        // Sync checker users to log (select subset of session users)
                        $checkerUsers = collect($sessionUsers)->random(min(2, count($sessionUsers)))->toArray();
                        $log->users()->sync($checkerUsers);
                    }
                }
            }
        }
    }
}
