<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            // ID del producto en el sistema externo. NUNCA se usa como
            // identificador interno (ver docs/architecture.md, Regla 9 del
            // prompt original: producto interno vs producto externo).
            $table->string('external_id');
            $table->json('external_data')->nullable();
            $table->string('sync_status')->default('pending'); // pending | synced | error
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'external_id']);
            $table->index(['tour_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_products');
    }
};
