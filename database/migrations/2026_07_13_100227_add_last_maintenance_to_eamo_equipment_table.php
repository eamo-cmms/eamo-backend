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
        Schema::table('eamo_equipment', function (Blueprint $table) {
            $table->json('last_maintenance')->nullable()->after('maintenance_interval_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_equipment', function (Blueprint $table) {
            $table->dropColumn('last_maintenance');
        });
    }
};
