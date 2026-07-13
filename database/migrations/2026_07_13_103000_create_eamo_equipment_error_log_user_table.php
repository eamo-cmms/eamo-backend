<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eamo_equipment_error_log_user', function (Blueprint $table) {
            $table->uuid('error_log_id');
            $table->uuid('user_id');

            $table->primary(['error_log_id', 'user_id']);

            $table->foreign('error_log_id')
                ->references('id')
                ->on('eamo_equipment_error_logs')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        if (Schema::hasColumn('eamo_equipment_error_logs', 'handler_id')) {
            Schema::table('eamo_equipment_error_logs', function (Blueprint $table) {
                $table->dropColumn('handler_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('eamo_equipment_error_logs', 'handler_id')) {
            Schema::table('eamo_equipment_error_logs', function (Blueprint $table) {
                $table->string('handler_id')->nullable();
            });
        }

        Schema::dropIfExists('eamo_equipment_error_log_user');
    }
};
