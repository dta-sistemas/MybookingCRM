<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Adulto", "Niño", "Con transporte", etc.
            $table->string('sku')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tour_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_variants');
    }
};
