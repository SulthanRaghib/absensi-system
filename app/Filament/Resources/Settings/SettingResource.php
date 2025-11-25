<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components as Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Lokasi';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Section::make('Pengaturan Lokasi Kantor')
                    ->description('Atur koordinat dan radius absensi')
                    ->schema([
                        Forms\TextInput::make('key')
                            ->label('Kunci Pengaturan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($context) => $context === 'edit')
                            ->helperText('Format: office_latitude, office_longitude, office_radius'),

                        Forms\TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->helperText('Masukkan nilai sesuai tipe data'),

                        Forms\Select::make('type')
                            ->label('Tipe Data')
                            ->options([
                                'string' => 'String',
                                'number' => 'Number',
                                'json' => 'JSON',
                                'boolean' => 'Boolean',
                            ])
                            ->default('string')
                            ->required(),

                        Forms\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'number') {
                            return number_format((float)$state, 6);
                        }
                        return $state;
                    })
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'number',
                        'success' => 'string',
                        'warning' => 'json',
                        'danger' => 'boolean',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Data')
                    ->options([
                        'string' => 'String',
                        'number' => 'Number',
                        'json' => 'JSON',
                        'boolean' => 'Boolean',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
