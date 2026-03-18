<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->visibleOn('create'),
                TextInput::make('stripe_account_id')
                    ->default(null),
                Toggle::make('stripe_account_active')
                    ->required(),
                Select::make('roles')
                    ->label('Role')
                    ->options(Role::pluck('name', 'name'))
                    ->searchable()
                    ->preload()
                    ->relationship('roles', 'name'),
            ]);
    }
}
