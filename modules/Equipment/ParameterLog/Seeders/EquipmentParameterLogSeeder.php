<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Masterdata\Equipment\Models\Equipment;

class EquipmentParameterLogSeeder extends Seeder
{
    public function run(): void
    {
        EquipmentParameterLog::query()->delete();

        $equipments = Equipment::with('equipmentParameters')->get();
        $userIds = User::pluck('id');

        foreach ($equipments as $equipment) {
            $parameters = $equipment->equipmentParameters;

            if ($parameters->isEmpty()) {
                continue;
            }

            // Seed logs for the last 7 days (including today)
            for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
                $date = CarbonImmutable::now()->subDays($daysAgo);

                foreach ($parameters as $parameter) {
                    // Generate 3 to 5 logs per parameter per day
                    $logCount = rand(3, 5);

                    for ($i = 0; $i < $logCount; $i++) {
                        $hour = rand(8, 17);
                        $minute = rand(0, 59);
                        $second = rand(0, 59);
                        $recordedAt = $date->setTime($hour, $minute, $second);

                        $min = $parameter->standard_min ?? ($parameter->standard ? $parameter->standard * 0.8 : 10.0);
                        $max = $parameter->standard_max ?? ($parameter->standard ? $parameter->standard * 1.2 : 100.0);

                        $randomVal = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
                        $value = (string) round($randomVal, 2);

                        EquipmentParameterLog::create([
                            'id' => (string) Str::uuid(),
                            'equipment_id' => $equipment->id,
                            'equipment_parameter_id' => $parameter->id,
                            'unit_id' => $parameter->unit_id,
                            'value' => $value,
                            'user_id' => $userIds->isNotEmpty() ? $userIds->random() : null,
                            'recorded_at' => $recordedAt,
                            'created_at' => $recordedAt,
                            'updated_at' => $recordedAt,
                        ]);
                    }
                }
            }
        }
    }
}
