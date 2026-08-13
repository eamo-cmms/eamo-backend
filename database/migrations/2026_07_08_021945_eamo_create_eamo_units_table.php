<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eamo_units', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eamo_units');
    }
};
