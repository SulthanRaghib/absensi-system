<?php

namespace App\Filament\User\Resources\Permissions\Tables;

use App\Filament\User\Resources\Permissions\Pages\EditPermission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe Perizinan')
                    ->badge()
                    ->colors([
                        'warning' => 'sakit',
                        'info' => 'izin',
                        'success' => 'dinas_luar',
                    ]),
                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Alasan Perizinan')
                    ->tooltip(fn($record) => $record->reason)
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
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
                EditAction::make()
                    ->hidden(fn($record) => $record->status !== 'pending'),
                ViewAction::make()
                    ->hidden(fn($record) => $record->status === 'pending'),
                DeleteAction::make()
                    ->hidden(fn($record) => $record->status !== 'pending'),
            ])
            ->recordUrl(
                fn($record) => $record->status === 'pending'
                    ? EditPermission::getUrl(['record' => $record])
                    : null
            )
            ->bulkActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
