<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\User;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Perizinan')
                    ->schema([
                        Select::make('user_id')
                            ->label('User Pemohon')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->disabled()
                            ->preload(),

                        Select::make('type')
                            ->label('Tipe Perizinan')
                            ->options([
                                'sakit' => 'Sakit',
                                'izin' => 'Izin',
                                'dinas_luar' => 'Dinas Luar',
                            ])
                            ->disabled()
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->disabled()
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->disabled()
                            ->afterOrEqual('start_date'),

                        Textarea::make('reason')
                            ->label('Alasan Perizinan')
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->label('Lampiran (gambar/PDF)')
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull()
                            ->openable()
                            ->downloadable()
                            ->previewable(true)
                            ->disabled()
                    ]),

                Section::make('Status Perizinan')
                    ->schema([
                        Actions::make([
                            Action::make('approve')
                                ->icon(Heroicon::OutlinedCheck)
                                ->color('success')
                                ->requiresConfirmation()
                                ->action(function ($record) {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_by' => Auth::id(),
                                    ]);
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
                                ->action(function ($record, array $data) {
                                    $record->update([
                                        'status' => 'rejected',
                                        'rejection_note' => $data['rejection_note'],
                                        'approved_by' => Auth::id(),
                                    ]);
                                })
                                ->hidden(fn($record) => $record->status !== 'pending'),
                        ])->fullWidth(),
                    ])
            ]);
    }
}
