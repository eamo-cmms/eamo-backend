<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('eamo_checklist_sessions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name');
            $table->string('equipment_id', 36);
            $table->datetime('session_date')->nullable();
            $table->string('cycle_type', 255)->nullable();
            $table->integer('cycle_interval')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('eamo_equipment')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eamo_checklist_sessions');
    }
};
