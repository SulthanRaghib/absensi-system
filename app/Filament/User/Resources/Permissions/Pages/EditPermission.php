<?php

namespace App\Filament\User\Resources\Permissions\Pages;

use App\Filament\User\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        // Only allow deleting if the request is still pending
        if ($this->record->status !== 'pending') {
            return [];
        }

        return [
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        // Only allow saving if the requrest is still pending
        if ($this->record->status !== 'pending') {
            return [];
        }

        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
        ];
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        if ($this->record->status !== 'pending') {
            return;
        }

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
