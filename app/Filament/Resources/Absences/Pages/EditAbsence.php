<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAbsence extends EditRecord
{
    protected static string $resource = AbsenceResource::class;

    /**
     * Extract only the time part (HH:MM:SS) before hydrating the form
     * so the TimePicker fields show the correct time instead of a full datetime string.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['jam_masuk'])) {
            $data['jam_masuk'] = Carbon::parse($data['jam_masuk'])->setTimezone(config('app.timezone'))->format('H:i:s');
        }
        if (! empty($data['jam_pulang'])) {
            $data['jam_pulang'] = Carbon::parse($data['jam_pulang'])->setTimezone(config('app.timezone'))->format('H:i:s');
        }
        // schedule_jam_masuk is stored as HH:MM string — no cast needed, just ensure correct length
        if (! empty($data['schedule_jam_masuk'])) {
            $data['schedule_jam_masuk'] = substr((string) $data['schedule_jam_masuk'], 0, 5);
        }
        return $data;
    }

    /**
     * Combine the date from `tanggal` with the time from `jam_masuk`/`jam_pulang`
     * before persisting so the datetime column stores the correct full datetime.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tanggal = $data['tanggal'] ?? null;
        if ($tanggal) {
            if (! empty($data['jam_masuk'])) {
                $data['jam_masuk'] = $tanggal . ' ' . substr((string) $data['jam_masuk'], 0, 5) . ':00';
            }
            if (! empty($data['jam_pulang'])) {
                $data['jam_pulang'] = $tanggal . ' ' . substr((string) $data['jam_pulang'], 0, 5) . ':00';
            }
        }
        // Ensure schedule_jam_masuk is stored as HH:MM (TimePicker may include seconds)
        if (! empty($data['schedule_jam_masuk'])) {
            $data['schedule_jam_masuk'] = substr((string) $data['schedule_jam_masuk'], 0, 5);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
