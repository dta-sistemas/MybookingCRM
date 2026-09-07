<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->unsignedInteger('capacity_total');
            // capacity_booked se actualiza SIEMPRE dentro de una transacción
            // con lockForUpdate() sobre esta fila (ver docs/availability.md,
            // a documentar en Fase 4). Nunca se escribe directo sin lock.
            $table->unsignedInteger('capacity_booked')->default(0);
            $table->string('status')->default('open'); // open | closed | blackout
            $table->timestamps();

            // Evita duplicar el mismo horario para el mismo tour el mismo día.
            $table->unique(['tour_id', 'date', 'start_time'], 'availability_slot_unique');
            $table->index(['tour_id', 'date', 'status']);
        });

        // Constraint a nivel de base de datos como segunda línea de defensa
        // (además del lock aplicativo) para que nunca se pueda persistir
        // capacity_booked > capacity_total.
        DB::statement(
            'ALTER TABLE availability_slots ADD CONSTRAINT chk_capacity_not_exceeded CHECK (capacity_booked <= capacity_total)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};
