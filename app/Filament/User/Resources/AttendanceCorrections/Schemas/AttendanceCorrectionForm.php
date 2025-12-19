<?php

namespace App\Filament\User\Resources\AttendanceCorrections\Schemas;

use Dom\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
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
                        Hidden::make('status')
                            ->dehydrated(false),

                        DatePicker::make('date')
                            ->label('Tanggal Absen')
                            ->required()
                            ->maxDate(now())
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),

                        Select::make('type')
                            ->label('Jenis Koreksi')
                            ->options([
                                'check_in' => 'Lupa Absen Masuk',
                                'check_out' => 'Lupa Absen Pulang',
                                'full_day' => 'Lupa Keduanya',
                            ])
                            ->required()
                            ->reactive()
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),

                        TimePicker::make('correction_time_in')
                            ->label('Waktu Masuk')
                            ->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('type'), ['check_in', 'full_day']))
                            ->required(fn(Get $get) => in_array($get('type'), ['check_in', 'full_day']))
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),

                        TimePicker::make('correction_time_out')
                            ->label('Waktu Pulang')
                            ->seconds(false)
                            ->visible(fn(Get $get) => in_array($get('type'), ['check_out', 'full_day']))
                            ->required(fn(Get $get) => in_array($get('type'), ['check_out', 'full_day']))
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),

                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->columnSpanFull()
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),
                    ]),

                Section::make('Lampiran Bukti')
                    ->schema([
                        FileUpload::make('proof_image')
                            ->label('Bukti Pendukung (Foto/Dokumen)')
                            ->image()
                            ->directory('correction-proofs')
                            ->columnSpanFull()
                            ->openable()
                            ->downloadable()
                            ->disabled(fn(Get $get) => filled($get('status')) && $get('status') !== 'pending'),

                        Textarea::make('rejection_note')
                            ->label('Alasan Penolakan (Admin)')
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => filled($get('status')) && $get('status') === 'rejected')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }
}
