<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('status'),
                        TextEntry::make('store_name')
                            ->placeholder('-'),
                        ImageEntry::make('cover_image')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('store_address')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
            ]);
    }
}
