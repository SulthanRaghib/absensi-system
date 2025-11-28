<?php

namespace App\Filament\Exports;

use App\Models\Absence;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AbsenceExporter extends Exporter
{
    protected static ?string $model = Absence::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('tanggal')
                ->label('Date')
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d M Y')),

            ExportColumn::make('user.name')
                ->label('User Name'),

            ExportColumn::make('jam_masuk')
                ->label('Check In'),

            ExportColumn::make('jam_pulang')
                ->label('Check Out'),

            ExportColumn::make('status')
                ->label('Status')
                ->state(function (Absence $record): string {
                    if ($record->jam_masuk && $record->jam_pulang) {
                        return 'Selesai';
                    } elseif ($record->jam_masuk) {
                        return 'Bekerja';
                    }
                    return 'Belum Absen';
                }),

            ExportColumn::make('late_duration')
                ->label('Late Duration')
                ->state(fn() => '-'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your absence export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
