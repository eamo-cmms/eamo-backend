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
        Schema::create('eamo_checklist_session_users', function (Blueprint $table) {
            $table->string('checklist_session_id', 36);
            $table->uuid('user_id');
            $table->softDeletes();

            $table->foreign('checklist_session_id', 'fk_session_user_session')
                ->references('id')
                ->on('eamo_checklist_sessions')
                ->restrictOnDelete();

            $table->foreign('user_id', 'fk_session_user_user')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->primary(['checklist_session_id', 'user_id']);
        });

        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eamo_checklist_sessions', function (Blueprint $table) {
            $table->string('created_by', 36)->nullable();
        });

        Schema::dropIfExists('eamo_checklist_session_users');
    }
};
