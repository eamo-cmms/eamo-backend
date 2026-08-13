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
        // 1. Create eamo_checklist_schedules table
        Schema::create('eamo_checklist_schedules', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('equipment_id', 36);
            $table->string('checklist_session_id', 36);
            $table->string('checklist_detail_id', 36);
            $table->date('date');
            $table->boolean('is_rescheduled')->default(false);
            $table->date('original_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();

            $table->foreign('checklist_session_id')
                ->references('id')
                ->on('eamo_checklist_sessions')
                ->restrictOnDelete();

            $table->foreign('checklist_detail_id')
                ->references('id')
                ->on('eamo_checklist_details')
                ->restrictOnDelete();
        });

        // 2. Create eamo_checklist_schedule_user pivot table
        Schema::create('eamo_checklist_schedule_user', function (Blueprint $table) {
            $table->string('checklist_schedule_id', 36);
            $table->uuid('user_id');
            $table->softDeletes();

            $table->foreign('checklist_schedule_id', 'fk_chk_sched_user_sched')
                ->references('id')
                ->on('eamo_checklist_schedules')
                ->restrictOnDelete();

            $table->foreign('user_id', 'fk_chk_sched_user_user')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->primary(['checklist_schedule_id', 'user_id']);
        });

        // 3. Create eamo_checklist_logs table
        Schema::create('eamo_checklist_logs', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('checklist_schedule_id', 36)->nullable();
            $table->enum('result', ['pass', 'fail'])->default('fail')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('checklist_schedule_id')
                ->references('id')
                ->on('eamo_checklist_schedules')
                ->restrictOnDelete();
        });

        // 4. Create eamo_checklist_log_users table
        Schema::create('eamo_checklist_log_users', function (Blueprint $table) {
            $table->string('checklist_log_id', 36);
            $table->uuid('user_id');
            $table->softDeletes();

            $table->foreign('checklist_log_id', 'fk_log_user_log')
                ->references('id')
                ->on('eamo_checklist_logs')
                ->restrictOnDelete();

            $table->foreign('user_id', 'fk_log_user_user')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->primary(['checklist_log_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eamo_checklist_log_users');
        Schema::dropIfExists('eamo_checklist_logs');
        Schema::dropIfExists('eamo_checklist_schedule_user');
        Schema::dropIfExists('eamo_checklist_schedules');
    }
};
