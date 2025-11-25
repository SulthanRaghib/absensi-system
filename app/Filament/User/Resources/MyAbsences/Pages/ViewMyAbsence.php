<?php

namespace App\Filament\User\Resources\MyAbsenceResource\Pages;

use App\Filament\User\Resources\MyAbsenceResource;
use App\Filament\User\Resources\MyAbsences\MyAbsenceResource as MyAbsencesMyAbsenceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMyAbsence extends ViewRecord
{
    protected static string $resource = MyAbsencesMyAbsenceResource::class;
}
