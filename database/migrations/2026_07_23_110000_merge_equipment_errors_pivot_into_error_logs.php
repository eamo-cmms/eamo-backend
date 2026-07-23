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
        // 1. Make occurred_at nullable in eamo_equipment_error_logs
        Schema::table('eamo_equipment_error_logs', function (Blueprint $table): void {
            $table->dateTime('occurred_at')->nullable()->change();
        });

        // 2. Migrate existing pivot data from eamo_equipment_equipment_errors into eamo_equipment_error_logs as definition records
        if (Schema::hasTable('eamo_equipment_equipment_errors')) {
            $pivotRecords = DB::table('eamo_equipment_equipment_errors')->get();
            $now = now();

            foreach ($pivotRecords as $record) {
                // Check if a definition record already exists
                $exists = DB::table('eamo_equipment_error_logs')
                    ->where('equipment_id', $record->equipment_id)
                    ->where('equipment_error_id', $record->equipment_error_id)
                    ->whereNull('occurred_at')
                    ->exists();

                if (! $exists) {
                    DB::table('eamo_equipment_error_logs')->insert([
                        'id' => (string) Str::uuid(),
                        'equipment_id' => $record->equipment_id,
                        'equipment_error_id' => $record->equipment_error_id,
                        'occurred_at' => null,
                        'restarted_at' => null,
                        'handled_at' => null,
                        'is_synced' => false,
                        'created_at' => $record->created_at ?? $now,
                        'updated_at' => $record->updated_at ?? $now,
                        'deleted_at' => $record->deleted_at ?? null,
                    ]);
                }
            }

            // 3. Drop pivot table
            Schema::dropIfExists('eamo_equipment_equipment_errors');
        }
    }

    public function down(): void
    {
        // Re-create pivot table if rolling back
        if (! Schema::hasTable('eamo_equipment_equipment_errors')) {
            Schema::create('eamo_equipment_equipment_errors', function (Blueprint $table): void {
                $table->string('equipment_id', 36);
                $table->string('equipment_error_id', 36);
                $table->primary(['equipment_id', 'equipment_error_id']);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('equipment_id')->references('id')->on('eamo_equipment')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreign('equipment_error_id')->references('id')->on('eamo_equipment_errors')->restrictOnDelete()->cascadeOnUpdate();
            });

            // Copy definition records back to pivot table
            $definitions = DB::table('eamo_equipment_error_logs')
                ->whereNull('occurred_at')
                ->get();

            foreach ($definitions as $def) {
                if ($def->equipment_error_id) {
                    DB::table('eamo_equipment_equipment_errors')->insertOrIgnore([
                        'equipment_id' => $def->equipment_id,
                        'equipment_error_id' => $def->equipment_error_id,
                        'created_at' => $def->created_at,
                        'updated_at' => $def->updated_at,
                        'deleted_at' => $def->deleted_at,
                    ]);
                }
            }

            // Delete definition records from error logs
            DB::table('eamo_equipment_error_logs')->whereNull('occurred_at')->delete();
        }
    }
};
