<?php

namespace App\Filament\User\Resources\Permissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Permission;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Pengajuan Perizinan')
                    ->schema([
                        Hidden::make('status')
                            ->dehydrated(false),

                        Select::make('type')
                            ->label('Tipe Perizinan')
                            ->options([
                                'sakit' => 'Sakit',
                                'izin' => 'Izin',
                                'dinas_luar' => 'Dinas Luar',
                            ])
                            ->required()
                            ->disabled(fn(Get $get) => $get('status') !== 'pending')
                            ->columnSpan(4),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->rule(function (Get $get, $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $userId = Auth::id();
                                    $startDate = $value;
                                    $endDate = $get('end_date');

                                    if (!$endDate) return;

                                    $query = Permission::where('user_id', $userId)
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->where(function ($query) use ($startDate, $endDate) {
                                            $query->whereBetween('start_date', [$startDate, $endDate])
                                                ->orWhereBetween('end_date', [$startDate, $endDate])
                                                ->orWhere(function ($query) use ($startDate, $endDate) {
                                                    $query->where('start_date', '<=', $startDate)
                                                        ->where('end_date', '>=', $endDate);
                                                });
                                        });

                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }

                                    if ($query->exists()) {
                                        $fail('You already have a pending or approved leave request for this period.');
                                    }
                                };
                            })
                            ->disabled(fn(Get $get) => $get('status') !== 'pending')
                            ->columnSpan(2),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date')
                            ->disabled(fn(Get $get) => $get('status') !== 'pending')
                            ->columnSpan(2),
                        Textarea::make('reason')
                            ->label('Alasan Perizinan')
                            ->required()
                            ->disabled(fn(Get $get) => $get('status') !== 'pending')
                            ->columnSpan(4),

                    ])
                    ->columns(4),
                Section::make('Bukti Pendukung')
                    ->schema([
                        FileUpload::make('attachment')
                            ->label('Lampiran (gambar/PDF)')
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->helperText('Silahkan unggah lampiran perizinan Anda, bisa berupa Chat WhatsApp dengan Mentor atau Dokumen pendukung lainnya.')
                            ->columnSpanFull()
                            ->openable()
                            ->downloadable()
                            ->previewable(true)
                            ->disabled(fn(Get $get) => $get('status') !== 'pending'),
                    ])
                    ->columns(1)
            ]);
    }
}
