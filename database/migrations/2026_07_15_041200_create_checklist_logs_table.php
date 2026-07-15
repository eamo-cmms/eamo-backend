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
        Schema::create('eamo_checklist_logs', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('checklist_detail_id', 36);
            $table->enum('result', ['pass', 'fail'])->nullable();
            $table->timestamps();

            $table->foreign('checklist_detail_id')
                ->references('id')
                ->on('eamo_checklist_details')
                ->cascadeOnDelete();
        });

        Schema::create('eamo_checklist_log_users', function (Blueprint $table) {
            $table->string('checklist_log_id', 36);
            $table->uuid('user_id');

            $table->foreign('checklist_log_id', 'fk_log_user_log')
                ->references('id')
                ->on('eamo_checklist_logs')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'fk_log_user_user')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->primary(['checklist_log_id', 'user_id']);
        });

        Schema::table('eamo_checklist_details', function (Blueprint $table) {
            $table->dropColumn('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_checklist_details', function (Blueprint $table) {
            $table->enum('result', ['pass', 'fail'])->nullable();
        });

        Schema::dropIfExists('eamo_checklist_log_users');
        Schema::dropIfExists('eamo_checklist_logs');
    }
};
