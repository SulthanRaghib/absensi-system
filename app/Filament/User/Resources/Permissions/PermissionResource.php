<?php

namespace App\Filament\User\Resources\Permissions;

use App\Filament\User\Resources\Permissions\Pages\CreatePermission;
use App\Filament\User\Resources\Permissions\Pages\EditPermission;
use App\Filament\User\Resources\Permissions\Pages\ListPermissions;
use App\Filament\User\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\User\Resources\Permissions\Tables\PermissionsTable;
use App\Models\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Perizinan';
    protected static ?string $modelLabel = 'Perizinan';
    protected static ?string $pluralModelLabel = 'Perizinan';
    protected static UnitEnum|string|null $navigationGroup = 'Absen & Riwayat';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
