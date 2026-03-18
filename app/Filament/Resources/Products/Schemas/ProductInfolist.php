<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info')
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('slug'),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('department.name')
                            ->label('Department'),
                        TextEntry::make('category.name')
                            ->label('Category'),
                        TextEntry::make('price')
                            ->money(),
                        TextEntry::make('status'),
                        TextEntry::make('quantity')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('created_by')
                            ->numeric(),
                        TextEntry::make('updated_by')
                            ->numeric(),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Product $record): bool => $record->trashed()),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
            ]);
    }
}
