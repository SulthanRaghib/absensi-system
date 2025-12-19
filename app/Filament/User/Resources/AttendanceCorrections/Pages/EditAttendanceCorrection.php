<?php

namespace App\Filament\User\Resources\AttendanceCorrections\Pages;

use App\Filament\User\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceCorrection extends EditRecord
{
    protected static string $resource = AttendanceCorrectionResource::class;

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
        // Only allow saving if the request is still pending
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
            Notification::make()
                ->danger()
                ->title('Tidak dapat mengubah')
                ->body('Pengajuan sudah ditolak atau telah disetujui oleh admin dan tidak dapat diubah.')
                ->send();

            return;
        }

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
