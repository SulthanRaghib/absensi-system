<?php

namespace App\Filament\User\Resources\AttendanceCorrections;

use App\Filament\User\Resources\AttendanceCorrections\Pages\CreateAttendanceCorrection;
use App\Filament\User\Resources\AttendanceCorrections\Pages\EditAttendanceCorrection;
use App\Filament\User\Resources\AttendanceCorrections\Pages\ListAttendanceCorrections;
use App\Filament\User\Resources\AttendanceCorrections\Schemas\AttendanceCorrectionForm;
use App\Filament\User\Resources\AttendanceCorrections\Tables\AttendanceCorrectionsTable;
use App\Models\AttendanceCorrection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceCorrectionResource extends Resource
{
    protected static ?string $model = AttendanceCorrection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Koreksi Absen';
    protected static ?string $modelLabel = 'Koreksi Absen';
    protected static ?string $pluralModelLabel = 'Koreksi Absen';
    protected static UnitEnum|string|null $navigationGroup = 'Absen & Riwayat';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'reason';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', \Illuminate\Support\Facades\Auth::id());
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', \Illuminate\Support\Facades\Auth::id())->where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Koreksi Absen Pending';
    }

    public static function form(Schema $schema): Schema
    {
        return AttendanceCorrectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceCorrectionsTable::configure($table);
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
            'index' => ListAttendanceCorrections::route('/'),
            'create' => CreateAttendanceCorrection::route('/create'),
            'edit' => EditAttendanceCorrection::route('/{record}/edit'),
        ];
    }
}
