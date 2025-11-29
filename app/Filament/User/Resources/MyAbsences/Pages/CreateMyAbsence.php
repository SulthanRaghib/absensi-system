<?php

namespace App\Filament\User\Resources\MyAbsences\Pages;

use App\Filament\User\Resources\MyAbsences\MyAbsenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyAbsence extends CreateRecord
{
    protected static string $resource = MyAbsenceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
