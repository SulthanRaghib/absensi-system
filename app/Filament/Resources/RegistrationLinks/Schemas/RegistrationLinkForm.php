<?php

namespace App\Filament\Resources\RegistrationLinks\Schemas;

use Filament\Forms\Components as Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RegistrationLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\TextInput::make('token')
                    ->default(fn() => Str::random(32))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),

                Forms\DateTimePicker::make('expires_at')
                    ->label('Expiration Time')
                    ->required()
                    ->native(false)
                    ->default(now()->addDay()),

                Forms\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
