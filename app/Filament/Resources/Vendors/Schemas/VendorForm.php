<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Enums\VendorStatusEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('status')
                    ->options(VendorStatusEnum::class)
                    ->preload()
                    ->searchable()
                    ->required(),
                TextInput::make('store_name')
                    ->default(null),
                FileUpload::make('cover_image')
                    ->directory('vendors/images')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->imageEditor()
                    ->image(),
                TextInput::make('store_address')
                    ->default(null),
            ]);
    }
}
