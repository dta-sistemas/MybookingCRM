<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo mínimo para satisfacer la relación Tour::providerProducts().
 * El mapeo real con Bokun/Viator/GetYourGuide se implementa en la
 * capa de integración (Fase 10-12).
 */
class ProviderProduct extends Model
{
    protected $fillable = [
        'tour_id',
        'provider_id',
        'external_id',
        'external_data',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'external_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
