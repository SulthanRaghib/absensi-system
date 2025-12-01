<?php

namespace App\Filament\Resources\RegistrationLinks\Pages;

use App\Filament\Resources\RegistrationLinks\RegistrationLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistrationLink extends CreateRecord
{
    protected static string $resource = RegistrationLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
