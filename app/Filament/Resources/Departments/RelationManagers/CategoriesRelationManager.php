<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    public function table(Table $table): Table
    {
        return CategoriesTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => CategoryResource::getUrl('create', ['department_id' => $this->ownerRecord->id])),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
