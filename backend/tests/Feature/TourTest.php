<?php

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('crea un tour y genera el slug automáticamente si no se proporciona', function () {
    $tour = Tour::create([
        'name' => 'Ultimate 5x1 Combo',
        'status' => Tour::STATUS_DRAFT,
    ]);

    expect($tour->slug)->toBe('ultimate-5x1-combo');
});

it('genera un slug único si ya existe uno igual', function () {
    Tour::create(['name' => 'ATV Adventure', 'status' => Tour::STATUS_DRAFT]);
    $second = Tour::create(['name' => 'ATV Adventure', 'status' => Tour::STATUS_DRAFT]);

    expect($second->slug)->toBe('atv-adventure-1');
});

it('respeta el slug proporcionado manualmente', function () {
    $tour = Tour::create([
        'name' => 'Chichen Itza Tour',
        'slug' => 'mi-slug-personalizado',
        'status' => Tour::STATUS_DRAFT,
    ]);

    expect($tour->slug)->toBe('mi-slug-personalizado');
});

it('actualiza los campos de un tour existente', function () {
    $tour = Tour::create(['name' => 'Tulum Tour', 'status' => Tour::STATUS_DRAFT]);

    $tour->update([
        'status' => Tour::STATUS_ACTIVE,
        'featured' => true,
        'duration_minutes' => 480,
    ]);

    expect($tour->fresh())
        ->status->toBe(Tour::STATUS_ACTIVE)
        ->featured->toBeTrue()
        ->duration_minutes->toBe(480);
});

it('asocia categorías a un tour mediante la tabla pivote', function () {
    $tour = Tour::create(['name' => 'Cozumel Snorkel', 'status' => Tour::STATUS_ACTIVE]);
    $category = Category::create(['name' => 'Snorkel']);

    $tour->categories()->attach($category);

    expect($tour->fresh()->categories)->toHaveCount(1);
    expect($tour->fresh()->categories->first()->name)->toBe('Snorkel');
});

it('el scope active solo devuelve tours activos', function () {
    Tour::create(['name' => 'Activo 1', 'status' => Tour::STATUS_ACTIVE]);
    Tour::create(['name' => 'Borrador 1', 'status' => Tour::STATUS_DRAFT]);

    expect(Tour::active()->count())->toBe(1);
});

it('hace soft delete de un tour sin borrarlo físicamente', function () {
    $tour = Tour::create(['name' => 'Isla Mujeres Tour', 'status' => Tour::STATUS_ACTIVE]);

    $tour->delete();

    expect(Tour::find($tour->id))->toBeNull();
    expect(Tour::withTrashed()->find($tour->id))->not->toBeNull();
});
