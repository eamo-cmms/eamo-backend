<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eamo_equipment_error_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('eamo_equipment_error_logs', 'is_synced')) {
                $table->dropColumn('is_synced');
            }
            if (! Schema::hasColumn('eamo_equipment_error_logs', 'is_handled')) {
                $table->boolean('is_handled')->default(false)->after('handler_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eamo_equipment_error_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('eamo_equipment_error_logs', 'is_handled')) {
                $table->dropColumn('is_handled');
            }
            if (! Schema::hasColumn('eamo_equipment_error_logs', 'is_synced')) {
                $table->boolean('is_synced')->default(false)->after('handler_id');
            }
        });
    }
};
