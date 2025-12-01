<?php

namespace App\Filament\Resources\RegistrationLinks;

use App\Filament\Resources\RegistrationLinks\Pages\CreateRegistrationLink;
use App\Filament\Resources\RegistrationLinks\Pages\EditRegistrationLink;
use App\Filament\Resources\RegistrationLinks\Pages\ListRegistrationLinks;
use App\Filament\Resources\RegistrationLinks\Schemas\RegistrationLinkForm;
use App\Filament\Resources\RegistrationLinks\Tables\RegistrationLinksTable;
use App\Models\RegistrationLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegistrationLinkResource extends Resource
{
    protected static ?string $model = RegistrationLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'RegistrationLink';

    public static function form(Schema $schema): Schema
    {
        return RegistrationLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrationLinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrationLinks::route('/'),
            'create' => CreateRegistrationLink::route('/create'),
            'edit' => EditRegistrationLink::route('/{record}/edit'),
        ];
    }
}
