<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eamo_maintenance_logs', function (Blueprint $table) {
            $table->string('equipment_id', 36)->nullable()->after('id')->index();
            $table->string('maintenance_schedule_id', 36)->nullable()->change();
            $table->uuid('user_id')->nullable()->after('maintenance_schedule_id')->index();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        // Backfill equipment_id from existing maintenance schedules if any
        try {
            DB::statement("
                UPDATE eamo_maintenance_logs
                SET equipment_id = eamo_maintenance_schedules.equipment_id
                FROM eamo_maintenance_schedules
                WHERE eamo_maintenance_logs.maintenance_schedule_id = eamo_maintenance_schedules.id
                  AND eamo_maintenance_logs.equipment_id IS NULL
            ");
        } catch (\Throwable $e) {
            // Ignore if tables are empty or driver differs
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_maintenance_logs', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'equipment_id',
                'user_id',
            ]);
            $table->string('maintenance_schedule_id', 36)->nullable(false)->change();
        });
    }
};


