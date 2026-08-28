<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentError;
use Modules\Masterdata\Equipment\Models\EquipmentState;

class EquipmentErrorSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch available users for error handling technicians
        $users = User::all();
        $userIds = $users->pluck('id')->toArray();
        if (empty($userIds)) {
            $userIds = [(string) Str::uuid()];
        }

        // 2. Fetch all equipments and select exactly 5 target equipments
        $allEquipments = Equipment::all();
        if ($allEquipments->isEmpty()) {
            $this->command?->warn('No equipment found to seed errors.');
            return;
        }

        $targetCodes = [
            'CNC-HAAS-VF2',
            'CNC-MAZAK-QT250',
            'IMM-ARBURG-370',
            'ROB-FANUC-M20',
            'PRS-AIDA-NC1',
        ];

        $targetEquipments = $allEquipments->whereIn('code', $targetCodes);
        if ($targetEquipments->count() < 5) {
            // Fallback: take first 5 equipments if specified codes are not found
            $targetEquipments = $allEquipments->take(5);
        }

        $targetEquipments = $targetEquipments->values();

        // 3. Define 10 industrial master errors with 100% complete descriptions & realistic Pareto weights
        $errorsData = [
            [
                'id' => 'emergency_stop',
                'name' => 'Dừng khẩn cấp (Emergency Stop)',
                'weight' => 30, // 30% frequency
                'reason' => 'Nút dừng khẩn cấp E-Stop được kích hoạt tại bàn điều khiển hoặc cảm biến rào chắn an toàn quang điện bị vi phạm khi có vật cản.',
                'fix' => 'Kiểm tra an toàn toàn bộ khu vực làm việc của máy, xác định nguyên nhân kích hoạt, xoay nhả nút dừng E-Stop và reset rơ-le an toàn hệ thống.',
                'protection_measures' => 'Huấn luyện quy trình an toàn vận hành định kỳ cho công nhân, kiểm tra độ nhạy của rơ-le an toàn và công tắc hành trình trước mỗi ca làm việc.',
            ],
            [
                'id' => 'feeder_jam',
                'name' => 'Kẹt cơ cấu cấp / gắp phôi (Feeder / Gripper Jam)',
                'weight' => 22, // 22% frequency
                'reason' => 'Phôi cấp bị lệch vị trí trong máng dẫn, kích thước phôi thô vượt dung sai tiêu chuẩn hoặc cảm biến vị trí kẹp bám mạt kim loại.',
                'fix' => 'Dừng chuyển động tay máy/cơ cấu nạp, gắp phôi kẹt ra khỏi cụm kẹp, làm sạch bề mặt cảm biến quang và bôi trơn ray trượt dẫn hướng.',
                'protection_measures' => 'Kiểm tra dung sai kích thước lô phôi đầu vào, vệ sinh khay cấp phôi sau mỗi ca làm việc, kiểm tra áp suất khí nén cấp cho xi lanh kẹp.',
            ],
            [
                'id' => 'air_pressure_low',
                'name' => 'Áp suất khí nén cấp không đủ (Insufficient Air Pressure)',
                'weight' => 18, // 18% frequency
                'reason' => 'Tụt áp trên đường ống cấp khí nén trung tâm nhà xưởng, cốc lọc bẫy hơi bị nghẹt nước đọng hoặc rò rỉ tại cút nối nhanh.',
                'fix' => 'Xả nước đọng ở cốc lọc khí, kiểm tra độ kín các cút nối khí nén và hiệu chỉnh lại van điều áp lên ngưỡng tiêu chuẩn (6.0 - 7.0 bar).',
                'protection_measures' => 'Kiểm tra rò rỉ đường ống khí định kỳ hàng tuần, xả đáy bình tích khí trung tâm mỗi ngày và thay lõi lọc khí 6 tháng/lần.',
            ],
            [
                'id' => 'safety_door_open',
                'name' => 'Lỗi cảm biến an toàn cửa mở (Safety Door Interlock Fault)',
                'weight' => 10, // 10% frequency
                'reason' => 'Cửa bảo vệ buồng gia công chưa đóng khít hoàn toàn hoặc tiếp điểm công tắc từ liên động an toàn bị lệch vị trí, bám bẩn dầu mỡ.',
                'fix' => 'Đóng khít hoàn toàn cửa buồng máy, lau sạch bề mặt tiếp xúc công tắc từ an toàn và kiểm tra giắc cắm tín hiệu liên động.',
                'protection_measures' => 'Kiểm tra độ nhạy và cơ cấu khóa cơ khí của liên động an toàn cửa hàng ngày, nghiêm cấm tháo bỏ hoặc bypass cảm biến cửa.',
            ],
            [
                'id' => 'auto_lube_low',
                'name' => 'Mức dầu bôi trơn tự động thấp (Low Auto-Lubrication Level)',
                'weight' => 8, // 8% frequency
                'reason' => 'Bình chứa dầu bôi trơn rãnh trượt và vít me bi sắp cạn hoặc bơm cấp dầu bôi trơn tự động bị nghẹt đường ống cấp.',
                'fix' => 'Châm thêm dầu bôi trơn rãnh trượt chuyên dụng (Slideway Oil ISO VG 68) vào bình cấp tự động và kích hoạt bơm xả bọt khí đường ống.',
                'protection_measures' => 'Kiểm tra mắt thăm dầu bôi trơn trước khi khởi động máy đầu mỗi ca, vệ sinh lưới lọc bình dầu bôi trơn mỗi 500h làm việc.',
            ],
            [
                'id' => 'motor_overheat',
                'name' => 'Quá nhiệt động cơ chính (Main Motor Overheating)',
                'weight' => 4, // 4% frequency
                'reason' => 'Động cơ vận hành quá tải liên tục trong thời gian dài hoặc hệ thống quạt làm mát cưỡng bức bị bám bụi bẩn làm giảm lưu lượng gió.',
                'fix' => 'Tạm dừng máy để động cơ hạ nhiệt độ an toàn, dùng khí nén vệ sinh quạt và cánh tản nhiệt, đo kiểm tra dòng điện 3 pha của động cơ.',
                'protection_measures' => 'Bảo dưỡng định kỳ hệ thống giải nhiệt, kiểm tra độ nhớt mỡ bôi trơn bạc đạn động cơ và lắp cảm biến nhiệt độ cảnh báo sớm.',
            ],
            [
                'id' => 'hydraulic_low',
                'name' => 'Áp suất dầu thủy lực thấp (Low Hydraulic Pressure)',
                'weight' => 3, // 3% frequency
                'reason' => 'Mức dầu thủy lực trong bồn chứa dưới ngưỡng an toàn, bộ lọc hút bị nghẹt hoặc đường ống van điều khiển bị rò rỉ áp suất.',
                'fix' => 'Bổ sung dầu thủy lực đúng chủng loại (ISO VG 46), siết chặt các cút nối cao áp, vệ sinh lưới lọc hút và kiểm tra cụm bơm áp lực.',
                'protection_measures' => 'Kiểm tra mức dầu hàng ngày qua thước đo mức bồn dầu, thay lõi lọc dầu thủy lực định kỳ theo lịch bảo trì tiêu chuẩn 1000h.',
            ],
            [
                'id' => 'spindle_vibration',
                'name' => 'Rung lắc trục chính bất thường (Abnormal Spindle Vibration)',
                'weight' => 2, // 2% frequency
                'reason' => 'Vòng bi trục chính bị mòn rơ cơ khí hoặc chi tiết đồ gá phôi / dao cắt bị mất cân bằng động khi quay ở tốc độ cao.',
                'fix' => 'Kiểm tra độ đảo trục chính bằng đồng hồ so, siết lại mâm cặp / bầu kẹp dao và cân nhắc thay thế cụm vòng bi trục chính nếu có tiếng ồn rơ.',
                'protection_measures' => 'Thực hiện cân bằng động định kỳ cho bầu kẹp dao, đo kiểm độ rung trục chính hàng tuần bằng máy đo rung chuyên dụng.',
            ],
            [
                'id' => 'electrical_trip',
                'name' => 'Quá tải dòng điện / Ngắt Aptomat (Electrical Overload Trip)',
                'weight' => 2, // 2% frequency
                'reason' => 'Dao động sụt điện áp nguồn lưới hoặc cơ cấu truyền động bị kẹt cơ khí đột ngột dẫn đến quá dòng ngắt rơ-le nhiệt bảo vệ.',
                'fix' => 'Đo kiểm tra điện trở cách điện cuộn dây, kiểm tra điện áp nguồn cấp 3 pha đủ pha, loại bỏ kẹt cơ khí và reset rơ-le nhiệt / aptomat.',
                'protection_measures' => 'Lắp đặt rơ-le bảo vệ mất pha và quá/thấp áp nguồn lưới, bảo dưỡng tủ điện điều khiển và siết lại đầu cosse định kỳ 6 tháng.',
            ],
            [
                'id' => 'limit_switch_err',
                'name' => 'Lỗi cảm biến giới hạn hành trình (Limit Switch Sensor Error)',
                'weight' => 1, // 1% frequency
                'reason' => 'Cảm biến tiệm cận giới hạn hành trình trục bị bám phoi cắt kim loại hoặc giắc cắm dây cáp tín hiệu điều khiển bị lỏng chập chờn.',
                'fix' => 'Vệ sinh sạch phoi kim loại bám trên đầu đọc cảm biến, kiểm tra độ hở khe từ cảm ứng và siết chặt giắc cắm cáp tín hiệu điều khiển.',
                'protection_measures' => 'Bọc bảo vệ đường xích dẫn cáp tránh tiếp xúc phoi nóng và dầu tưới nguội, kiểm tra độ chắc chắn chân đế cảm biến định kỳ.',
            ],
            [
                'id' => 'short_stop',
                'name' => 'Dừng ngắn bất thường (Short Stop)',
                'weight' => 15, // 15% frequency
                'reason' => 'Cảm biến đầu vào ngắt tín hiệu tạm thời do phôi qua nhanh, nghẽn dòng cấp liệu tức thời hoặc công nhân tạm dừng chỉnh đồ gá.',
                'fix' => 'Kiểm tra luồng nạp liệu, làm sạch mắt đọc quang phát hiện phôi và nhấn nút tiếp tục chu trình tự động (Cycle Start).',
                'protection_measures' => 'Căn chỉnh khoảng cách phôi cấp liệu đồng đều, cài đặt bộ đếm thời gian trễ chống dội tín hiệu cảm biến quang.',
            ],
        ];

        // 4. Create or update master EquipmentError records
        $createdErrors = [];
        foreach ($errorsData as $item) {
            $error = EquipmentError::query()
                ->withoutGlobalScopes()
                ->where('id', $item['id'])
                ->orWhere('name', $item['name'])
                ->first();

            if (! $error) {
                $error = EquipmentError::create([
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'reason' => $item['reason'],
                    'fix' => $item['fix'],
                    'protection_measures' => $item['protection_measures'],
                ]);
            } else {
                $error->update([
                    'name' => $item['name'],
                    'reason' => $item['reason'],
                    'fix' => $item['fix'],
                    'protection_measures' => $item['protection_measures'],
                ]);
            }

            $createdErrors[$error->id] = [
                'model' => $error,
                'weight' => $item['weight'],
            ];
        }

        // 5. Clean up old error logs and pivot user links to ensure accurate seeding
        DB::table('eamo_equipment_error_log_user')->delete();
        EquipmentErrorLog::withTrashed()->forceDelete();

        // 6. Build weighted lottery pool for natural Pareto sampling using actual error IDs
        $weightedPool = [];
        foreach ($createdErrors as $actualErrorId => $info) {
            for ($w = 0; $w < $info['weight']; $w++) {
                $weightedPool[] = $actualErrorId;
            }
        }

        // 7. Define uneven log count distribution across the 5 target machines (total ~60 logs)
        // Machine 0: Heavy load (22 logs, has 1 active error)
        // Machine 1: Medium load (15 logs, has 1 active error)
        // Machine 2: Normal load (12 logs, all resolved)
        // Machine 3: Light load (7 logs, all resolved)
        // Machine 4: Light load (4 logs, all resolved)
        $machineLogConfigs = [
            ['count' => 22, 'has_active_error' => true],
            ['count' => 15, 'has_active_error' => true],
            ['count' => 12, 'has_active_error' => false],
            ['count' => 7,  'has_active_error' => false],
            ['count' => 4,  'has_active_error' => false],
        ];

        $now = CarbonImmutable::now();
        $totalLogsCreated = 0;

        foreach ($targetEquipments as $index => $equipment) {
            $config = $machineLogConfigs[$index] ?? ['count' => 5, 'has_active_error' => false];
            $numLogs = $config['count'];
            $hasActive = $config['has_active_error'];

            // Generate logs spanning the last 30 days
            for ($i = 0; $i < $numLogs; $i++) {
                $errorId = $weightedPool[array_rand($weightedPool)];
                $isCurrentActiveLog = ($hasActive && $i === 0);

                if ($isCurrentActiveLog) {
                    // Active incident: Occurred recently (20-60 mins ago), unresolved
                    $occurredAt = $now->subMinutes(rand(20, 60));
                    $handledAt = null;
                    $restartedAt = null;
                    $isHandled = false;
                } else {
                    // Historical resolved log: Occurred in the last 1 to 30 days during shift hours (08:00 - 17:00)
                    $daysAgo = rand(1, 30);
                    $hour = rand(8, 16);
                    $minute = rand(0, 59);
                    $second = rand(0, 59);
                    $occurredAt = $now->subDays($daysAgo)->setTime($hour, $minute, $second);

                    // Resolution duration between 15 minutes and 110 minutes
                    $durationMinutes = rand(15, 110);
                    $handledAt = $occurredAt->addMinutes($durationMinutes);
                    $restartedAt = $handledAt->addMinutes(rand(1, 5));
                    $isHandled = true;
                }

                $logId = (string) Str::uuid();

                $log = EquipmentErrorLog::create([
                    'id' => $logId,
                    'equipment_id' => $equipment->id,
                    'equipment_error_id' => $errorId,
                    'occurred_at' => $occurredAt,
                    'restarted_at' => $restartedAt,
                    'handled_at' => $handledAt,
                    'is_handled' => $isHandled,
                ]);

                // Sync 1-2 technicians to the error log
                $assignedCount = rand(1, min(2, count($userIds)));
                $assignedUsers = collect($userIds)->random($assignedCount)->toArray();
                $log->handlers()->sync($assignedUsers);

                // If handled, soft delete to match StoreEquipmentErrorLogService business logic
                if ($isHandled) {
                    $log->delete();
                }

                $totalLogsCreated++;
            }

            // Update EquipmentState: 'Fault' if machine has an active open error, 'Running' if all resolved
            $stateModel = EquipmentState::where('equipment_id', $equipment->id)->first();
            $targetState = $hasActive ? 'Fault' : 'Running';

            if ($stateModel) {
                $stateModel->update(['state' => $targetState]);
            } else {
                EquipmentState::create([
                    'id' => (string) Str::uuid(),
                    'equipment_id' => $equipment->id,
                    'state' => $targetState,
                ]);
            }
        }

        $this->command?->info("Successfully seeded {$totalLogsCreated} error logs across exactly 5 equipments with natural Pareto distribution.");
        $this->command?->info("Equipments with active 'Fault' status: 2 (having open active incidents).");
    }
}

