<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductVariationTypesEnum;
use App\Filament\Resources\Products\ProductResource;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $navigationLabel = 'Variations';

    protected static ?string $title = 'Variation Types';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;

    public static function getNavigationLabel(): string
    {
        return 'Product Variation Types';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('variationTypes')
                    ->relationship()
                    ->collapsible()
                    ->defaultItems(1)
                    ->addActionLabel('Add new variation type')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('type')
                            ->options(ProductVariationTypesEnum::labels())
                            ->required()
                            ->live(),
                        Repeater::make('options')
                            ->relationship()
                            ->collapsible()
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpan(2)
                                    ->required(),
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->panelLayout('grid')
                                    ->collection('images')
                                    ->reorderable()
                                    ->appendFiles()
                                    ->preserveFilenames()
                                    ->columnSpan(3)
                                    ->hidden(fn ($get) => $get('../../type') !== 'image'),
                            ])
                            ->columnSpan(2),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
