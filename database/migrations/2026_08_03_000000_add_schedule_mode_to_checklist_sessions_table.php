<?php

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
            $table->string('schedule_mode', 20)->default('repeating')->after('session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->dropColumn('schedule_mode');
        });
    }
};
