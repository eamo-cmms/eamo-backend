<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });

        Schema::table('eamo_maintenance_plans', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });

        Schema::table('eamo_operating_times', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });

        Schema::table('eamo_equipment_parameter_logs', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });

        Schema::table('eamo_equipment_error_logs', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();

            $table->foreign('equipment_error_id')
                ->references('id')
                ->on('eamo_equipment_errors')
                ->restrictOnDelete();
        });

        Schema::table('eamo_equipment_parameter_logs', function (Blueprint $table) {
            $table->foreign('equipment_parameter_id')
                ->references('id')
                ->on('eamo_equipment_parameters')
                ->restrictOnDelete();
        });

        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_equipment_error_logs', function (Blueprint $table) {
            $table->dropForeign(['equipment_error_id']);
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('eamo_equipment_parameter_logs', function (Blueprint $table) {
            $table->dropForeign(['equipment_parameter_id']);
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('eamo_operating_times', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('eamo_maintenance_plans', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });

        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });
    }
};
