<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->boolean('is_auto_generated')->default(true)->after('equipment_id');
            $table->boolean('is_adhoc')->default(false)->after('is_auto_generated');
            $table->text('notes')->nullable()->after('is_rescheduled');
        });
    }

    public function down(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->dropColumn(['is_auto_generated', 'is_adhoc', 'notes']);
        });
    }
};
