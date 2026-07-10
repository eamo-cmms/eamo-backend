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
        Schema::table('eamo_maintenance_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('occurrences')->nullable()->after('cycle_interval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_maintenance_plans', function (Blueprint $table) {
            $table->dropColumn('occurrences');
        });
    }
};
