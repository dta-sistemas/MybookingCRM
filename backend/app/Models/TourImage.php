<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourImage extends Model
{
    protected $fillable = [
        'tour_id',
        'path',
        'alt_text',
        'is_cover',
        'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Garantiza que solo exista una imagen de portada por tour.
        static::saving(function (TourImage $image) {
            if ($image->is_cover) {
                static::where('tour_id', $image->tour_id)
                    ->when($image->id, fn ($q) => $q->where('id', '!=', $image->id))
                    ->update(['is_cover' => false]);
            }
        });
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
