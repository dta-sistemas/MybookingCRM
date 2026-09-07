<?php

namespace App\Filament\Resources\TourResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variantes y precios';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre de la variante')
                ->placeholder('Adulto, Niño, Con transporte...')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]),

            Forms\Components\Repeater::make('prices')
                ->relationship()
                ->label('Precios')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->required()
                            ->prefix('$'),

                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'USD' => 'USD',
                                'MXN' => 'MXN',
                            ])
                            ->default('USD')
                            ->required(),

                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Vigente desde'),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Vigente hasta'),
                    ]),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Variante')->searchable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('prices_count')
                    ->label('# Precios')
                    ->counts('prices'),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->reorderable('sort_order');
    }
}
