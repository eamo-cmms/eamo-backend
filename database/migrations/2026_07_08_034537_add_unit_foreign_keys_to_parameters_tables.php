<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Detect all distinct non-null unit_id string values currently stored in both tables.
        $table1UnitIds = DB::table('eamo_equipment_parameters')
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->toArray();

        $allRawUnits = array_unique($table1UnitIds);

        $unitMapping = [];
        foreach ($allRawUnits as $rawUnit) {
            if (Str::isUuid($rawUnit)) {
                // If it is already a UUID, check if it exists in units table, if not insert it
                $exists = DB::table('eamo_units')->where('id', $rawUnit)->exists();
                if (! $exists) {
                    DB::table('eamo_units')->insert([
                        'id' => $rawUnit,
                        'name' => 'Unit '.$rawUnit,
                        'code' => $rawUnit,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $unitMapping[$rawUnit] = $rawUnit;
            } else {
                // It is a code like '°C', 'kW', 'bar'.
                // Check if a unit with this code already exists.
                $existingUnit = DB::table('eamo_units')->where('code', $rawUnit)->first();
                if ($existingUnit) {
                    $uuid = $existingUnit->id;
                } else {
                    $uuid = (string) Str::uuid();
                    DB::table('eamo_units')->insert([
                        'id' => $uuid,
                        'name' => $rawUnit,
                        'code' => $rawUnit,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $unitMapping[$rawUnit] = $uuid;
            }
        }

        // Update the eamo_equipment_parameters and eamo_standard_parameters table unit_id columns to the correct UUID.
        foreach ($unitMapping as $rawUnit => $uuid) {
            if ($rawUnit !== $uuid) {
                DB::table('eamo_equipment_parameters')
                    ->where('unit_id', $rawUnit)
                    ->update(['unit_id' => $uuid]);
            }
        }

        // 2. Add foreign key constraints.
        Schema::table('eamo_equipment_parameters', function (Blueprint $table): void {
            $table->foreign('unit_id')
                ->references('id')
                ->on('eamo_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::table('eamo_equipment_parameters', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
        });

    }
};
