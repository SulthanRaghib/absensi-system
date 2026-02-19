<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalBiasaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SettingResource::class;

    protected string $view = 'filament.resources.settings.pages.jadwal-biasa-settings';

    protected static ?string $title = 'Pengaturan Jam Kerja Normal';

    public ?array $data = [];

    public function mount(): void
    {
        $schedule = Setting::getDefaultSchedule();

        $this->form->fill([
            'default_jam_masuk'        => $schedule['jam_masuk'],
            'default_jam_pulang'       => $schedule['jam_pulang'],
            'default_jam_pulang_jumat' => $schedule['jam_pulang_jumat'],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Jam Kerja Normal (Hari Biasa)')
                    ->description('Atur jam masuk dan jam pulang untuk hari kerja normal (Senin – Jumat). Pengaturan ini berlaku di luar periode Ramadan. Perubahan langsung berlaku tanpa perlu deployment ulang.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(3)->schema([
                            TimePicker::make('default_jam_masuk')
                                ->label('Jam Masuk (Senin – Kamis)')
                                ->placeholder('07:30')
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->required()
                                ->helperText('Batas waktu tepat waktu untuk hari Senin sampai Kamis.'),

                            TimePicker::make('default_jam_pulang')
                                ->label('Jam Pulang (Senin – Kamis)')
                                ->placeholder('16:00')
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->required()
                                ->helperText('Jam pulang untuk hari Senin sampai Kamis.'),

                            TimePicker::make('default_jam_pulang_jumat')
                                ->label('Jam Pulang Jumat')
                                ->placeholder('16:30')
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->required()
                                ->helperText('Jam pulang khusus hari Jumat (biasanya lebih awal).'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $normalizeTime = fn($val) => $val ? substr((string) $val, 0, 5) : null;

        $jamMasuk        = $normalizeTime($data['default_jam_masuk']);
        $jamPulang       = $normalizeTime($data['default_jam_pulang']);
        $jamPulangJumat  = $normalizeTime($data['default_jam_pulang_jumat']);

        if (! $jamMasuk || ! $jamPulang || ! $jamPulangJumat) {
            Notification::make()
                ->title('Semua jam harus diisi.')
                ->danger()
                ->send();
            return;
        }

        Setting::saveDefaultSchedule($jamMasuk, $jamPulang, $jamPulangJumat);

        Notification::make()
            ->title('Jam kerja normal berhasil disimpan')
            ->body("Jam masuk: {$jamMasuk} | Jam pulang: {$jamPulang} | Jumat: {$jamPulangJumat}")
            ->success()
            ->send();

        $this->redirect(SettingResource::getUrl('index'));
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->getFormActions();
    }
}
