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
            $table->string('cycle_type', 255)->nullable();
            $table->integer('cycle_interval')->nullable();
        });

        // 2. Create eamo_checklist_schedules table
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

        // 3. Create eamo_checklist_schedule_user pivot table
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

        // 4. Update eamo_checklist_logs to point to eamo_checklist_schedules
        Schema::table('eamo_checklist_logs', function (Blueprint $table) {
            $table->dropForeign(['checklist_detail_id']);
            $table->dropColumn('checklist_detail_id');
            $table->string('checklist_schedule_id', 36)->nullable()->after('id');
            $table->enum('status', ['pending', 'completed'])->default('pending')->after('result');
            $table->timestamp('checked_at')->nullable()->after('status');

            $table->foreign('checklist_schedule_id')
                ->references('id')
                ->on('eamo_checklist_schedules')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rollback eamo_checklist_logs changes
        Schema::table('eamo_checklist_logs', function (Blueprint $table) {
            $table->dropForeign(['checklist_schedule_id']);
            $table->dropColumn(['checklist_schedule_id', 'status', 'checked_at']);
            $table->string('checklist_detail_id', 36)->after('id');

            $table->foreign('checklist_detail_id')
                ->references('id')
                ->on('eamo_checklist_details')
                ->cascadeOnDelete();
        });

        // 2. Drop pivot table
        Schema::dropIfExists('eamo_checklist_schedule_user');

        // 3. Drop eamo_checklist_schedules
        Schema::dropIfExists('eamo_checklist_schedules');

        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->dropColumn(['cycle_type', 'cycle_interval']);
        });
    }
};
