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
            $table->softDeletes();

            $table->foreign('error_log_id')
                ->references('id')
                ->on('eamo_equipment_error_logs')
                ->restrictOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eamo_equipment_error_log_user');
    }
};
