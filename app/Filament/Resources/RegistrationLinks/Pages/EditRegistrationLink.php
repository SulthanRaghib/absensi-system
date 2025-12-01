<?php

namespace App\Filament\Resources\RegistrationLinks\Pages;

use App\Filament\Resources\RegistrationLinks\RegistrationLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationLink extends EditRecord
{
    protected static string $resource = RegistrationLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
