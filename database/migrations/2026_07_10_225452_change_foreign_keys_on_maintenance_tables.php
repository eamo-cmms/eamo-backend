<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['maintenance_item_id']);
            $table->foreign('maintenance_item_id')
                ->references('id')
                ->on('eamo_maintenance_items')
                ->cascadeOnDelete();
        });

        Schema::table('eamo_maintenance_logs', function (Blueprint $table) {
            $table->dropForeign(['maintenance_schedule_id']);
            $table->foreign('maintenance_schedule_id')
                ->references('id')
                ->on('eamo_maintenance_schedules')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['maintenance_item_id']);
            $table->foreign('maintenance_item_id')
                ->references('id')
                ->on('eamo_maintenance_items')
                ->nullOnDelete();
        });

        Schema::table('eamo_maintenance_logs', function (Blueprint $table) {
            $table->dropForeign(['maintenance_schedule_id']);
            $table->foreign('maintenance_schedule_id')
                ->references('id')
                ->on('eamo_maintenance_schedules')
                ->cascadeOnDelete();
        });
    }
};
