<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RamadanSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SettingResource::class;

    protected string $view = 'filament.resources.settings.pages.ramadan-settings';

    protected static ?string $title = 'Pengaturan Jadwal Khusus (Ramadan)';

    public ?array $data = [];

    public function mount(): void
    {
        $ramadan = Setting::getRamadanSettings();

        // start_date/end_date are Carbon instances (or null) — DatePicker needs Y-m-d string
        // jam_masuk/jam_pulang are already H:i strings (or null)
        $this->form->fill([
            'ramadan_start_date' => $ramadan['start_date']?->toDateString(),
            'ramadan_end_date'   => $ramadan['end_date']?->toDateString(),
            'ramadan_jam_masuk'  => $ramadan['jam_masuk'],
            'ramadan_jam_pulang' => $ramadan['jam_pulang'],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Pengaturan Jam Khusus (Ramadan)')
                    ->description('Atur jadwal check-in dan check-out khusus selama bulan Ramadan. Jika hari ini berada dalam rentang tanggal ini, sistem akan menggunakan jam yang ditentukan di sini. Kosongkan semua field untuk menonaktifkan.')
                    ->icon('heroicon-o-moon')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('ramadan_start_date')
                                ->label('Tanggal Mulai Ramadan')
                                ->placeholder('Pilih tanggal mulai')
                                ->displayFormat('d/m/Y')
                                ->native(false)
                                ->nullable(),

                            DatePicker::make('ramadan_end_date')
                                ->label('Tanggal Selesai Ramadan')
                                ->placeholder('Pilih tanggal selesai')
                                ->displayFormat('d/m/Y')
                                ->native(false)
                                ->nullable()
                                ->afterOrEqual('ramadan_start_date'),
                        ]),

                        Grid::make(2)->schema([
                            TimePicker::make('ramadan_jam_masuk')
                                ->label('Jam Masuk Ramadan')
                                ->placeholder('Contoh: 08:00')
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->nullable(),

                            TimePicker::make('ramadan_jam_pulang')
                                ->label('Jam Pulang Ramadan')
                                ->placeholder('Contoh: 16:30')
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->nullable(),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // DatePicker returns Y-m-d string; TimePicker returns H:i (seconds(false))
        // Strip to HH:MM in case browser returns H:i:s
        $normalizeTime = fn($val) => $val ? substr((string) $val, 0, 5) : null;

        Setting::saveRamadanSettings(
            $data['ramadan_start_date'] ?: null,
            $data['ramadan_end_date']   ?: null,
            $normalizeTime($data['ramadan_jam_masuk']),
            $normalizeTime($data['ramadan_jam_pulang']),
        );

        Notification::make()
            ->title('Pengaturan Ramadan berhasil disimpan!')
            ->success()
            ->send();

        $this->redirect(SettingResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->action('save'),
        ];
    }
}
