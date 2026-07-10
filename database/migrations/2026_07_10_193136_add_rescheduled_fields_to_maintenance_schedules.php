<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->boolean('is_rescheduled')->default(false)->after('date');
            $table->date('original_date')->nullable()->after('is_rescheduled');
        });

        // Backfill: set original_date = date for all existing schedules
        DB::table('eamo_maintenance_schedules')
            ->whereNull('original_date')
            ->update(['original_date' => DB::raw('date')]);
    }

    public function down(): void
    {
        Schema::table('eamo_maintenance_schedules', function (Blueprint $table) {
            $table->dropColumn(['is_rescheduled', 'original_date']);
        });
    }
};
