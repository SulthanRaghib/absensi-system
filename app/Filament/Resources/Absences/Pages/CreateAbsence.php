<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAbsence extends CreateRecord
{
    protected static string $resource = AbsenceResource::class;

    /**
     * Combine the date from `tanggal` with the time from `jam_masuk`/`jam_pulang`
     * so the datetime column stores the correct full datetime (e.g. 2026-02-19 09:00:00)
     * instead of defaulting to today's date.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
