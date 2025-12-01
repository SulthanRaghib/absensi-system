<?php

namespace App\Filament\Resources\RegistrationLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class RegistrationLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('token')
                    ->limit(20)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('qr_code')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading('Registration QR Code')
                    ->modalContent(fn($record): View => view('filament.resources.registration-links.qr-modal', [
                        'url' => route('register.intern', $record->token),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
