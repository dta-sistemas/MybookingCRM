<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_variant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            // Vigencia: permite temporadas alta/baja sin reescribir el esquema.
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tour_variant_id', 'is_active', 'valid_from', 'valid_until'], 'prices_variant_validity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
