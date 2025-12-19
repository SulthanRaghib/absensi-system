<?php

namespace App\Filament\User\Resources\AttendanceCorrections\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AttendanceCorrectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Pengajuan Koreksi')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Tanggal Absen')
                            ->required()
                            ->maxDate(now()),

                        Select::make('type')
                            ->label('Jenis Koreksi')
                            ->options([
                                'check_in' => 'Lupa Absen Masuk',
                                'check_out' => 'Lupa Absen Pulang',
                                'full_day' => 'Lupa Keduanya',
                            ])
                            ->required()
                            ->reactive(),

                        TimePicker::make('correction_time_in')
                            ->label('Waktu Masuk')
                            ->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('type'), ['check_in', 'full_day']))
                            ->required(fn(Get $get) => in_array($get('type'), ['check_in', 'full_day'])),

                        TimePicker::make('correction_time_out')
                            ->label('Waktu Pulang')
                            ->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('type'), ['check_out', 'full_day']))
                            ->required(fn(Get $get) => in_array($get('type'), ['check_out', 'full_day'])),

                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('proof_image')
                            ->label('Bukti Pendukung (Foto/Dokumen)')
                            ->image()
                            ->directory('correction-proofs')
                            ->columnSpanFull()
                            ->openable()
                            ->downloadable(),
                    ])
                    ->columns(2),
            ]);
    }
}
