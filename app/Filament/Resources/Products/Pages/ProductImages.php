<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductImages extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Images';

    protected static ?string $navigationLabel = 'Images';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Photo;

    public static function getNavigationLabel(): string
    {
        return 'Product Images';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('images')
                    ->image()
                    ->multiple()
                    ->openable()
                    ->panelLayout('grid')
                    ->collection('images')
                    ->reorderable()
                    ->appendFiles()
                    ->preserveFilenames()
                    ->columnSpan(2),
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
