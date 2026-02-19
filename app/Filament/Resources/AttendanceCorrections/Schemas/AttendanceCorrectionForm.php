<?php

namespace App\Filament\Resources\AttendanceCorrections\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Models\Absence;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;

class AttendanceCorrectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pengajuan')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pegawai')
                            ->disabled(),

                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->disabled(),

                        TextInput::make('type')
                            ->label('Jenis')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'check_in' => 'Lupa Absen Masuk',
                                'check_out' => 'Lupa Absen Pulang',
                                'full_day' => 'Lupa Keduanya',
                                default => $state,
                            })
                            ->disabled(),

                        TimePicker::make('correction_time_in')
                            ->label('Waktu Masuk')
                            ->seconds(false)
                            ->disabled(),

                        TimePicker::make('correction_time_out')
                            ->label('Waktu Pulang')
                            ->seconds(false)
                            ->disabled(),

                        Textarea::make('reason')
                            ->label('Alasan')
                            ->columnSpanFull()
                            ->disabled(),

                        FileUpload::make('proof_image')
                            ->label('Bukti')
                            ->image()
                            ->directory('correction-proofs')
                            ->columnSpanFull()
                            ->disabled()
                            ->openable()
                            ->downloadable(),
                    ])
                    ->columns(2),
                Section::make('Status Pengajuan')
                    ->schema([
                        Actions::make([
                            Action::make('approve')
                                ->icon(Heroicon::OutlinedCheck)
                                ->color('success')
                                ->requiresConfirmation()
                                ->action(function ($record, $livewire) {
                                    $record->update([
                                        'status'      => 'approved',
                                        'approved_by' => Auth::id(),
                                    ]);

                                    // Resolve the schedule that was active on the correction date.
                                    // This correctly picks Ramadan schedule when applicable.
                                    $service     = new AttendanceService();
                                    $dateCarbon  = Carbon::parse($record->date);
                                    $daySchedule = $service->getScheduleForDate($dateCarbon);

                                    $absence = Absence::firstOrCreate(
                                        [
                                            'user_id' => $record->user_id,
                                            'tanggal' => $record->date,
                                        ],
                                        [
                                            'jam_masuk'          => null,
                                            'jam_pulang'         => null,
                                            'schedule_jam_masuk' => $daySchedule['jam_masuk'],
                                            'is_ramadan'         => $daySchedule['is_ramadan'],
                                        ]
                                    );

                                    // Apply corrected times based on request type.
                                    // Combine the date with the time to ensure full datetime is stored correctly.
                                    $dateStr = $record->date->format('Y-m-d');

                                    if (($record->type === 'check_in' || $record->type === 'full_day') && $record->correction_time_in) {
                                        $absence->jam_masuk = $dateStr . ' ' . $record->correction_time_in->format('H:i:s');
                                    }

                                    if (($record->type === 'check_out' || $record->type === 'full_day') && $record->correction_time_out) {
                                        $absence->jam_pulang = $dateStr . ' ' . $record->correction_time_out->format('H:i:s');
                                    }

                                    // Always stamp the correct schedule snapshot so lateness
                                    // is evaluated against the right threshold (Ramadan or normal).
                                    $absence->schedule_jam_masuk = $daySchedule['jam_masuk'];
                                    $absence->is_ramadan         = $daySchedule['is_ramadan'];

                                    $absence->save();

                                    Notification::make()->success()->title('Pengajuan Disetujui')->send();

                                    $livewire->redirect(AttendanceCorrectionResource::getUrl('index'));
                                })
                                ->hidden(fn($record) => $record->status !== 'pending'),
                            Action::make('reject')
                                ->icon(Heroicon::OutlinedXMark)
                                ->color('danger')
                                ->form([
                                    Textarea::make('rejection_note')
                                        ->required()
                                        ->label('Alasan Penolakan'),
                                ])
                                ->action(function ($record, array $data, $livewire) {
                                    $record->update([
                                        'status'         => 'rejected',
                                        'rejection_note' => $data['rejection_note'],
                                        'approved_by'    => Auth::id(),
                                    ]);

                                    Notification::make()->danger()->title('Pengajuan Ditolak')->send();

                                    $livewire->redirect(AttendanceCorrectionResource::getUrl('index'));
                                })
                                ->hidden(fn($record) => $record->status !== 'pending'),
                        ])->fullWidth(),
                    ])
            ]);
    }
}
