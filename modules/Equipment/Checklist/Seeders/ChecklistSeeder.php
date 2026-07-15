<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Masterdata\Equipment\Models\Equipment;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get all user IDs
        $users = User::pluck('id', null)->toArray();

        // 2. Fetch all equipment
        $equipments = Equipment::all();
        if ($equipments->isEmpty()) {
            $this->command->warn('No equipment found. Please run EquipmentSeeder first.');

            return;
        }

        // 3. Define common checklists items description
        $checklistItems = [
            'Visual inspection of machine body for damage',
            'Verify emergency stop switch function',
            'Check coolant / fluid levels',
            'Verify safety guard sensors and interlocks',
            'Check power cables and grounding wires',
            'Verify calibration parameters',
        ];

        // 4. Seed checklist sessions and details
        foreach ($equipments as $equipment) {
            // Seed 2 sessions per equipment (one from yesterday, one from today)
            for ($i = 0; $i < 2; $i++) {
                $sessionDate = Carbon::now()->subDays($i)->subHours(rand(0, 8));

                $session = ChecklistSession::create([
                    'id' => (string) Str::uuid(),
                    'name' => 'Checklist - '.$equipment->name,
                    'equipment_id' => $equipment->id,
                    'session_date' => $sessionDate,
                ]);

                if (! empty($users)) {
                    // Sync 1 to 2 random users
                    $sessionUsers = (array) array_rand(array_flip($users), rand(1, min(2, count($users))));
                    $session->users()->sync($sessionUsers);
                }

                // Create checklist details for this session
                foreach ($checklistItems as $index => $description) {
                    $result = rand(0, 5) === 0 ? 'fail' : 'pass'; // 16.7% fail rate
                    $failReason = $result === 'fail' ? 'Minor wear detected or out of spec' : null;

                    $detail = ChecklistDetail::create([
                        'id' => (string) Str::uuid(),
                        'session_id' => $session->id,
                        'checklist_id' => 'CHK-ID-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                        'description' => $failReason ?? $description,
                    ]);

                    $log = $detail->logs()->create([
                        'result' => $result,
                        'created_at' => $sessionDate,
                        'updated_at' => $sessionDate,
                    ]);

                    if (! empty($sessionUsers)) {
                        $log->users()->sync($sessionUsers);
                    }
                }
            }
        }

        $this->command->info('Checklist sessions and details seeded successfully!');
    }
}
