<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info')
                    ->schema([
                        TextEntry::make('department_id')
                            ->numeric(),
                        TextEntry::make('parent.name')
                            ->label('Parent')
                            ->placeholder('-'),
                        TextEntry::make('name'),
                        IconEntry::make('active')
                            ->boolean(),
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
