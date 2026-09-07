<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_slot_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('expires_at');
            // booking_id se agrega en una migración posterior (add_booking_id_to_inventory_holds),
            // una vez que la tabla `bookings` existe, para respetar el orden de FKs.
            $table->timestamps();

            $table->index(['availability_slot_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_holds');
    }
};
