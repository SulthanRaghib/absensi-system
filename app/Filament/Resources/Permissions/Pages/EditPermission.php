<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function approveFromForm(): void
    {
        if ($this->record->status !== 'pending') {
            Notification::make()->warning()->title('Tidak dapat menyetujui')->body('Permintaan sudah diproses.')->send();

            return;
        }

        $this->record->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        Notification::make()->success()->title('Permintaan Disetujui')->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function rejectFromForm(string $reason): void
    {
        if ($this->record->status !== 'pending') {
            Notification::make()->warning()->title('Tidak dapat menolak')->body('Permintaan sudah diproses.')->send();

            return;
        }

        $this->record->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'rejection_note' => $reason,
        ]);

        Notification::make()->danger()->title('Permintaan Ditolak')->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
