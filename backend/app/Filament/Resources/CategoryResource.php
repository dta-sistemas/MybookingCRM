<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Tours';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $state, Forms\Set $set, ?Category $record) {
                    if (blank($record)) {
                        $set('slug', Category::generateUniqueSlug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Forms\Components\Textarea::make('description')
                ->label('Descripción')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label('Activa')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
                Tables\Columns\TextColumn::make('tours_count')
                    ->label('# Tours')
                    ->counts('tours'),
                Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
