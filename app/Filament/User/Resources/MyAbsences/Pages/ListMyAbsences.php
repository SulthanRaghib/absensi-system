<?php

namespace App\Filament\User\Resources\MyAbsences\Pages;

use App\Filament\User\Resources\MyAbsences\MyAbsenceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListMyAbsences extends ListRecords
{
    protected static string $resource = MyAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('absensi')
                ->label('Lakukan Absensi')
                ->icon('heroicon-o-finger-print')
                ->color('success')
                ->url(route('absensi.index'))
                ->openUrlInNewTab(false),
        ];
    }
}
