<?php

namespace App\Filament\Resources\TourResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Imágenes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')
                ->label('Imagen')
                ->image()
                ->directory('tours')
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('alt_text')
                ->label('Texto alternativo (SEO/accesibilidad)')
                ->maxLength(255),

            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_cover')
                ->label('Usar como portada')
                ->helperText('Solo una imagen puede ser portada; marcar esta desmarca las demás automáticamente.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('path')->label('Imagen'),
                Tables\Columns\TextColumn::make('alt_text')->label('Alt text'),
                Tables\Columns\IconColumn::make('is_cover')->label('Portada')->boolean(),
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
