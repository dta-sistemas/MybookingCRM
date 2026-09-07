<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo mínimo para satisfacer la relación Tour::availabilitySlots().
 * La lógica de negocio (cálculo de disponibilidad, locks de concurrencia,
 * generación de slots) se implementa en la Fase 4 — Availability + Inventory.
 */
class AvailabilitySlot extends Model
{
    protected $fillable = [
        'tour_id',
        'date',
        'start_time',
        'capacity_total',
        'capacity_booked',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
