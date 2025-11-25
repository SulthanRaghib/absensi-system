<?php

namespace App\Filament\User\Resources\MyAbsences\Pages;

use App\Filament\User\Resources\MyAbsences\MyAbsenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMyAbsence extends EditRecord
{
    protected static string $resource = MyAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
