<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use App\Filament\Exports\AbsenceExporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListAbsences extends ListRecords
{
    protected static string $resource = AbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_monthly')
                ->label('Laporan Bulanan')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('Pengguna')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('month')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ])
                        ->default(now()->month)
                        ->required(),
                    Select::make('year')
                        ->label('Tahun')
                        ->options(function () {
                            $years = range(now()->year - 5, now()->year + 1);
                            return array_combine($years, $years);
                        })
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('absences.export-monthly', [
                        'month' => $data['month'],
                        'year' => $data['year'],
                        'user_id' => $data['user_id'] ?? null,
                    ]);
                }),
            // ExportAction::make()
            //     ->exporter(AbsenceExporter::class)
            //     ->label('Export Data Mentah')
            //     ->color('success'),
            CreateAction::make(),
        ];
    }
}
