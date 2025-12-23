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
                                        'status' => 'approved',
                                        'approved_by' => Auth::id(),
                                    ]);

                                    $absence = Absence::firstOrCreate(
                                        [
                                            'user_id' => $record->user_id,
                                            'tanggal' => $record->date,
                                        ],
                                        [
                                            'jam_masuk' => null,
                                            'jam_pulang' => null,
                                        ]
                                    );

                                    if ($record->type === 'check_in' || $record->type === 'full_day') {
                                        $absence->jam_masuk = $record->correction_time_in;
                                    }

                                    if ($record->type === 'check_out' || $record->type === 'full_day') {
                                        $absence->jam_pulang = $record->correction_time_out;
                                    }

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
                                        'status' => 'rejected',
                                        'rejection_note' => $data['rejection_note'],
                                        'approved_by' => Auth::id(),
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
