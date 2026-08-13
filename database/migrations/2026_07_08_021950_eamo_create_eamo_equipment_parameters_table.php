<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eamo_equipment_parameters', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 32)->unique();
            $table->string('equipment_id', 36)->nullable();
            $table->string('product_category_id', 36)->nullable();
            $table->string('equipment_category_id', 36)->nullable();
            $table->string('unit_id', 36)->nullable();
            $table->string('name');
            $table->decimal('standard', 19, 4)->nullable();
            $table->decimal('standard_max', 19, 4)->nullable();
            $table->decimal('standard_min', 19, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')->references('id')->on('eamo_equipment')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('equipment_category_id')->references('id')->on('eamo_equipment_categories')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('unit_id')->references('id')->on('eamo_units')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eamo_equipment_parameters');
    }
};
