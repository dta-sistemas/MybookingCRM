<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourResource\Pages;
use App\Filament\Resources\TourResource\RelationManagers;
use App\Models\Tour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Tours';

    protected static ?string $navigationLabel = 'Tours';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Tour')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('General')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $state, Forms\Set $set, ?Tour $record) {
                                    if (blank($record)) {
                                        $set('slug', Tour::generateUniqueSlug($state));
                                    }
                                }),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),

                            Forms\Components\Textarea::make('short_description')
                                ->label('Descripción corta')
                                ->maxLength(500)
                                ->rows(2)
                                ->columnSpanFull(),

                            Forms\Components\RichEditor::make('description')
                                ->label('Descripción completa')
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('Duración (minutos)')
                                    ->numeric()
                                    ->minValue(1),

                                Forms\Components\TextInput::make('location')
                                    ->label('Ubicación'),

                                Forms\Components\Select::make('language')
                                    ->label('Idioma')
                                    ->options([
                                        'es' => 'Español',
                                        'en' => 'Inglés',
                                    ])
                                    ->default('es')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options(Tour::STATUSES)
                                    ->default(Tour::STATUS_DRAFT)
                                    ->required(),

                                Forms\Components\Toggle::make('featured')
                                    ->label('Destacado')
                                    ->inline(false),
                            ]),

                            Forms\Components\Select::make('categories')
                                ->label('Categorías')
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        ]),

                    Forms\Components\Tabs\Tab::make('SEO')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Meta título')
                                ->maxLength(255),

                            Forms\Components\Textarea::make('meta_description')
                                ->label('Meta descripción')
                                ->maxLength(500)
                                ->rows(3),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.path')
                    ->label('')
                    ->getStateUsing(fn (Tour $record) => optional($record->images->firstWhere('is_cover', true))->path)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Categorías')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Tour::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Tour::STATUS_ACTIVE => 'success',
                        Tour::STATUS_INACTIVE => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('featured')
                    ->label('Destacado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Tour::STATUSES),

                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Destacado'),

                Tables\Filters\SelectFilter::make('categories')
                    ->label('Categoría')
                    ->relationship('categories', 'name'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTours::route('/'),
            'create' => Pages\CreateTour::route('/create'),
            'edit' => Pages\EditTour::route('/{record}/edit'),
        ];
    }
}
