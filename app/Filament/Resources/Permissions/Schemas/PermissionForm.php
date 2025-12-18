<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\User;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User Pemohon')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->label('Tipe Perizinan')
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'dinas_luar' => 'Dinas Luar',
                    ])
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->afterOrEqual('start_date'),
                Textarea::make('reason')
                    ->label('Alasan Perizinan')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('attachment')
                    ->label('Lampiran (gambar/PDF)')
                    ->directory('leave-attachments')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->columnSpanFull()
                    ->openable()
                    ->downloadable()
                    ->previewable(true),
                Select::make('status')
                    ->label('Status Perizinan')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('rejection_note')
                    ->label('Catatan Penolakan')
                    ->columnSpanFull(),
            ]);
    }
}
