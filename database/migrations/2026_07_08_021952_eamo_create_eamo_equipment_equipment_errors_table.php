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
        Schema::create('eamo_equipment_equipment_errors', function (Blueprint $table) {
            $table->string('equipment_id', 36);
            $table->string('equipment_error_id', 36);

            $table->primary(['equipment_id', 'equipment_error_id']);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')->references('id')->on('eamo_equipment')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('equipment_error_id')->references('id')->on('eamo_equipment_errors')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eamo_equipment_equipment_errors');
    }
};
