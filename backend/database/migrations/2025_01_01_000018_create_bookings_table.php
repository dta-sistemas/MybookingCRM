<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // código público, ej. IC-2026-000123
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('tour_id')->constrained()->restrictOnDelete();
            $table->foreignId('availability_slot_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('pending');
            // PENDING | PAYMENT_PENDING | CONFIRMED | CANCELLED | COMPLETED | NO_SHOW
            $table->decimal('total_amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('availability_slot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
