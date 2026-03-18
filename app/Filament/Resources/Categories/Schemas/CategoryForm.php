<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(request('department_id'))
                    ->disabled(request()->filled('department_id'))
                    ->dehydrated()
                    ->preload()
                    ->searchable()
                    ->required(),

                Select::make('parent_id')
                    ->options(function ($get) {
                        $departmentId = $get('department_id');

                        if (! $departmentId) {
                            return [];
                        }

                        return Category::query()
                            ->where('department_id', $departmentId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->label('Parent Category')
                    ->preload()
                    ->searchable()
                    ->nullable()
                    ->default(null),

                TextInput::make('name')
                    ->required(),

                Toggle::make('active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
