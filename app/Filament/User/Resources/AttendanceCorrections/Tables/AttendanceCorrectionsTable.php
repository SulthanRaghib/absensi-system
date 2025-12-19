<?php

namespace App\Filament\User\Resources\AttendanceCorrections\Tables;

use App\Models\AttendanceCorrection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendanceCorrectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30),
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
                EditAction::make()
                    ->visible(fn(AttendanceCorrection $record) => $record->status === 'pending'),
                DeleteAction::make()
                    ->visible(fn(AttendanceCorrection $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
