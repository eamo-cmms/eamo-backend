<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Masterdata\Equipment\Models\Equipment;

class OperatingTimeSeeder extends Seeder
{
    public function run(): void
    {
        OperatingTime::query()->delete();

        $equipments = Equipment::all();

        foreach ($equipments as $equipment) {
            // Seed operating times for the last 15 days
            for ($daysAgo = 15; $daysAgo >= 1; $daysAgo--) {
                $date = CarbonImmutable::now()->subDays($daysAgo);

                // Shift starts at 08:00:00 and ends at 16:00:00 (8 hours)
                $startTime = $date->setTime(8, 0, 0);
                $endTime = $date->setTime(16, 0, 0);

                $workingTime = 8.0; // 8 hours
                $plannedStop = rand(0, 15) / 10; // 0.0 to 1.5 hours
                $unplannedStop = rand(0, 10) / 10; // 0.0 to 1.0 hours

                $plannedOp = max(0, $workingTime - $plannedStop);
                $actualOp = max(0, $plannedOp - $unplannedStop);
                $availabilityFactor = $plannedOp > 0 ? ($actualOp / $plannedOp) * 100 : 0;

                OperatingTime::create([
                    'id' => (string) Str::uuid(),
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                    'working_time' => $workingTime,
                    'planned_stop_time' => $plannedStop,
                    'unplanned_stop_time' => $unplannedStop,
                    'planned_operating_time' => $plannedOp,
                    'actual_operating_time' => $actualOp,
                    'availability_factor' => round($availabilityFactor, 2),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'date' => $date->toDateString(),
                ]);
            }
        }
    }
}
