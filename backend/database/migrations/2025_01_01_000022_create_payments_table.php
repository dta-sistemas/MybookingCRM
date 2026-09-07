<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('status')->default('pending'); // pending | paid | failed | refunded
            $table->string('provider')->nullable(); // stripe | conekta | ...
            // Solo referencia externa del proveedor de pagos; JAMÁS número
            // de tarjeta, CVV, ni datos sensibles (ver Sección 22 y 33).
            $table->string('provider_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
