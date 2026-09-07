<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'tour_variant_id',
        'amount',
        'currency',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(TourVariant::class, 'tour_variant_id');
    }
}
