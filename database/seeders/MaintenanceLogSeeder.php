<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Masterdata\Equipment\Models\Equipment;

class MaintenanceLogSeeder extends Seeder
{
    public function run(): void
    {
        $equipments = Equipment::all();
        if ($equipments->isEmpty()) {
            return;
        }

        $users = User::all();
        $user = $users->firstWhere('email', 'engineer@gmail.com')
            ?? $users->firstWhere('email', 'manager@gmail.com')
            ?? $users->first();

        $categories = MaintenanceCategory::all();
        $sampleCategory = $categories->first();

        // 1. Create a sample Maintenance Plan & Schedules for "Scheduled" logs
        if ($sampleCategory && $equipments->count() >= 3) {
            $sampleEquipment = $equipments->first();
            $plan = MaintenancePlan::firstOrCreate(
                ['plan_code' => 'PLAN-2026-Q3'],
                [
                    'equipment_id' => $sampleEquipment->id,
                    'maintenance_category_id' => $sampleCategory->id,
                    'maintenance_type' => 'Preventive',
                    'date' => Carbon::now()->subDays(20)->toDateString(),
                ]
            );

            $item = MaintenanceItem::firstOrCreate(
                ['name' => 'Kiểm tra dầu bôi trơn và bạc đạn'],
                ['maintenance_category_id' => $sampleCategory->id]
            );

            $schedule = MaintenanceSchedule::firstOrCreate(
                [
                    'maintenance_plan_id' => $plan->id,
                    'equipment_id' => $sampleEquipment->id,
                ],
                [
                    'maintenance_item_id' => $item->id,
                    'date' => Carbon::now()->subDays(15)->toDateString(),
                    'is_rescheduled' => false,
                ]
            );
        }

        $notesList = [
            'Bảo dưỡng tra dầu mỡ trục chính và kiểm tra độ rơ cơ khí.',
            'Thay thế cảm biến quang bị suy giảm tín hiệu sau 1000h chạy.',
            'Căn chỉnh dao cắt và kiểm tra hệ thống khí nén áp lực cao.',
            'Kiểm tra độ rung động cơ và siết lại toàn bộ bulong chân đế.',
            'Vệ sinh lưới lọc dầu thủy lực và bổ sung 5 lít dầu nhớt 68.',
            'Bảo trì định kỳ cấp 1 theo checklist tiêu chuẩn nhà máy.',
            'Khắc phục sự cố kẹt phôi tự động, thay lò xo hồi vị.',
            'Kiểm tra tiếp điểm contactor và thổi bụi tủ điện điều khiển.',
            'Hiệu chuẩn nhiệt độ buồng gia nhiệt và kiểm tra điện trở sấy.',
            'Thay thế dây curoa truyền động bị mòn rạn nứt.',
            'Kiểm tra áp suất bơm chân không và vệ sinh van điện từ.',
            'Đo điện trở cách điện motor và vệ sinh cánh quạt tản nhiệt.',
        ];

        $types = ['periodic', 'periodic', 'corrective', 'preventive', 'inspection'];

        // 2. Generate 35 realistic logs distributed across equipments
        $logsData = [];
        $now = Carbon::now();

        foreach ($equipments->take(12) as $index => $eq) {
            // Give top machines more logs to create prominent chart bars
            $numLogs = match ($index) {
                0 => 7,
                1 => 5,
                2 => 4,
                3 => 4,
                4 => 3,
                default => rand(1, 2),
            };

            for ($i = 0; $i < $numLogs; $i++) {
                $daysAgo = rand(1, 90);
                $logDate = $now->copy()->subDays($daysAgo)->toDateString();
                $type = $types[array_rand($types)];
                $note = $notesList[array_rand($notesList)];
                $assignedUser = $users->random();

                // 20% of logs link to schedule if available
                $scheduleId = (isset($schedule) && $index === 0 && $i === 0) ? $schedule->id : null;

                $log = MaintenanceLog::create([
                    'equipment_id'            => $eq->id,
                    'maintenance_schedule_id' => $scheduleId,
                    'user_id'                 => $assignedUser->id,
                    'log_date'                => $logDate,
                    'type'                    => $type,
                    'note'                    => $note,
                    'created_at'              => $now->copy()->subDays($daysAgo)->setTime(rand(8, 17), rand(0, 59)),
                    'updated_at'              => $now->copy()->subDays($daysAgo)->setTime(rand(8, 17), rand(0, 59)),
                ]);

                // Update equipment last_maintenance for the most recent log
                if ($i === 0) {
                    $eq->update([
                        'last_maintenance' => [
                            'datetime' => $logDate . ' 09:00:00',
                            'user_id'  => $assignedUser->id,
                            'note'     => $note,
                        ],
                    ]);
                }
            }
        }
    }
}
