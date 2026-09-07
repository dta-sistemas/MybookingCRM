<?php

use App\Models\Category;
use App\Models\Tour;
use App\Models\TourImage;
use App\Models\TourVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('genera slug único para categorías repetidas', function () {
    Category::create(['name' => 'Aventura']);
    $second = Category::create(['name' => 'Aventura']);

    expect($second->slug)->toBe('aventura-1');
});

it('solo permite una imagen de portada por tour', function () {
    $tour = Tour::create(['name' => 'Xcaret Tour', 'status' => Tour::STATUS_ACTIVE]);

    $first = TourImage::create(['tour_id' => $tour->id, 'path' => 'a.jpg', 'is_cover' => true]);
    $second = TourImage::create(['tour_id' => $tour->id, 'path' => 'b.jpg', 'is_cover' => true]);

    expect($first->fresh()->is_cover)->toBeFalse();
    expect($second->fresh()->is_cover)->toBeTrue();
});

it('crea variantes con precios asociados', function () {
    $tour = Tour::create(['name' => 'ATV Extreme', 'status' => Tour::STATUS_ACTIVE]);

    $variant = TourVariant::create([
        'tour_id' => $tour->id,
        'name' => 'Adulto',
        'is_active' => true,
    ]);

    $variant->prices()->create([
        'amount' => 79.99,
        'currency' => 'USD',
        'is_active' => true,
    ]);

    expect($variant->fresh()->prices)->toHaveCount(1);
    expect((float) $variant->fresh()->prices->first()->amount)->toBe(79.99);
});
