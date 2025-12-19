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
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),

                        Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->label('Disetujui Oleh')
                            ->nullable()
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }
}
