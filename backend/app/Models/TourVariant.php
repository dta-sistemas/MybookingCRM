<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourVariant extends Model
{
    protected $fillable = [
        'tour_id',
        'name',
        'sku',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Precio vigente hoy (is_active + dentro del rango valid_from/valid_until,
     * o sin rango definido). Útil para mostrarlo en el admin; el cálculo
     * "oficial" para cotizar/reservar se centraliza en un Service en Fase 5.
     */
    public function currentPrice(): HasMany
    {
        return $this->prices()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', now()))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()));
    }
}
