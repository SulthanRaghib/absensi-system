<?php

namespace App\Filament\Resources\AttendanceCorrections\Tables;

use App\Models\Absence;
use App\Models\AttendanceCorrection;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'check_in' => 'Lupa Masuk',
                        'check_out' => 'Lupa Pulang',
                        'full_day' => 'Lupa Keduanya',
                        default => $state,
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->options([
                        'today' => 'Hari Ini',
                        'this_week' => 'Minggu Ini',
                        'this_month' => 'Bulan Ini',
                        'this_year' => 'Tahun Ini',
                    ])
                    ->query(function ($query, $value) {
                        switch ($value) {
                            case 'today':
                                return $query->whereDate('created_at', now());
                            case 'this_week':
                                return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                            case 'this_month':
                                return $query->whereMonth('created_at', now()->month);
                            case 'this_year':
                                return $query->whereYear('created_at', now()->year);
                        }
                    }),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(AttendanceCorrection $record) => $record->status === 'pending')
                    ->action(function (AttendanceCorrection $record) {
                        // 1. Update Request Status
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                        ]);

                        // 2. Find or Create Absence Record
                        $absence = Absence::firstOrCreate(
                            [
                                'user_id' => $record->user_id,
                                'tanggal' => $record->date,
                            ],
                            [
                                // Defaults if creating new
                                'jam_masuk' => null,
                                'jam_pulang' => null,
                            ]
                        );

                        // 3. Update Absence based on Type
                        if ($record->type === 'check_in' || $record->type === 'full_day') {
                            $absence->jam_masuk = $record->correction_time_in;
                        }

                        if ($record->type === 'check_out' || $record->type === 'full_day') {
                            $absence->jam_pulang = $record->correction_time_out;
                        }

                        $absence->save();

                        // 4. Notify User
                        Notification::make()
                            ->title('Koreksi Absen Disetujui')
                            ->body("Pengajuan koreksi absen Anda untuk tanggal {$record->date->format('d M Y')} telah disetujui.")
                            ->success()
                            ->sendToDatabase($record->user);
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(AttendanceCorrection $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (AttendanceCorrection $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Koreksi Absen Ditolak')
                            ->body("Pengajuan koreksi absen Anda ditolak. Alasan: {$data['rejection_reason']}")
                            ->danger()
                            ->sendToDatabase($record->user);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
