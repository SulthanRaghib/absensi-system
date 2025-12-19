<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('approver.name')
                    ->label('Penyetuju')
                    ->placeholder('-'),
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
                EditAction::make(),
                ViewAction::make(),
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
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_note' => $data['rejection_note'],
                            'approved_by' => Auth::id(),
                        ]);
                    })
                    ->hidden(fn($record) => $record->status !== 'pending'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
