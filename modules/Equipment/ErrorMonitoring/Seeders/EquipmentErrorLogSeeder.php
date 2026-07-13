<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Masterdata\Equipment\Models\Equipment;

class EquipmentErrorLogSeeder extends Seeder
{
    public function run(): void
    {
        EquipmentErrorLog::query()->delete();

        // Get all users for handlers
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        // Get all equipment with their associated errors
        $equipments = Equipment::with('equipmentErrors')->get();

        foreach ($equipments as $equipment) {
            $errors = $equipment->equipmentErrors;
            if ($errors->isEmpty()) {
                continue;
            }

            // Create 3 to 10 random error logs for each equipment
            $logCount = rand(3, 10);
            for ($i = 0; $i < $logCount; $i++) {
                $error = $errors->random();

                // Random time in the last 30 days
                $occurredAt = CarbonImmutable::now()->subDays(rand(1, 30))->subHours(rand(1, 23))->subMinutes(rand(1, 59));

                // Active downtime duration (e.g. between 5 mins and 4 hours)
                $downtimeMinutes = rand(5, 240);
                $handledAt = $occurredAt->addMinutes($downtimeMinutes);
                $restartedAt = $handledAt->addMinutes(rand(1, 15)); // restarted slightly after handling

                $log = EquipmentErrorLog::create([
                    'id' => (string) Str::uuid(),
                    'equipment_id' => $equipment->id,
                    'equipment_error_id' => $error->id,
                    'occurred_at' => $occurredAt,
                    'handled_at' => $handledAt,
                    'restarted_at' => $restartedAt,
                    'is_synced' => (bool) rand(0, 1),
                ]);

                // Sync 1 or 2 random users as handlers
                $handlersCount = rand(1, 2);
                $log->handlers()->sync($users->random($handlersCount)->pluck('id')->toArray());
            }
        }
    }
}
