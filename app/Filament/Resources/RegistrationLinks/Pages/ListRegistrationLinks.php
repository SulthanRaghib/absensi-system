<?php

namespace App\Filament\Resources\RegistrationLinks\Pages;

use App\Filament\Resources\RegistrationLinks\RegistrationLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationLinks extends ListRecords
{
    protected static string $resource = RegistrationLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
