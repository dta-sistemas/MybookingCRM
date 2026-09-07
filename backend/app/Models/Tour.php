<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tour extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_INACTIVE => 'Inactivo',
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'duration_minutes',
        'location',
        'status',
        'featured',
        'language',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tour $tour) {
            if (empty($tour->slug)) {
                $tour->slug = static::generateUniqueSlug($tour->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_tour');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasMany
    {
        return $this->images()->where('is_cover', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(TourVariant::class)->orderBy('sort_order');
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function providerProducts(): HasMany
    {
        return $this->hasMany(ProviderProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
