<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->live(onBlur: true)
                    ->required()
                    ->afterStateUpdated(function ($operation, $state, $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        $set('category_id', null);
                    }),
                Select::make('category_id')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: function (Builder $query, $get) {
                            $department_id = $get('department_id');

                            if ($department_id) {
                                $query->where('department_id', $department_id);
                            }
                        }
                    )
                    ->label('Category')
                    ->preload()
                    ->searchable()
                    ->required(),
                RichEditor::make('description')
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                        'table',
                    ])
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value)
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->default(null),
                Section::make('SEO')
                    ->collapsible()
                    ->schema([
                        TextInput::make('meta_title'),
                        TextInput::make('meta_description'),
                    ])
                    ->columnSpan(2),
                // TextInput::make('created_by')
                //     ->required()
                //     ->numeric(),
                // TextInput::make('updated_by')
                //     ->required()
                //     ->numeric(),
            ]);
    }
}
