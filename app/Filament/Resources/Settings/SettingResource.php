<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Pengaturan Lokasi';

    protected static ?int $navigationSort = 3;

    /**
     * Only admin can access settings
     */
    public static function canViewAny(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Section::make('Pengaturan Lokasi Kantor')
                    ->description('Atur koordinat dan radius absensi')
                    ->schema([
                        Forms\Select::make('type')
                            ->label('Tipe Data')
                            ->options([
                                'string'  => 'String',
                                'number'  => 'Number',
                                'json'    => 'JSON',
                                'boolean' => 'Boolean',
                                'select'  => 'Select',
                                'date'    => 'Date',
                                'time'    => 'Time',
                            ])
                            ->default('string')
                            ->required()
                            ->live(),

                        Forms\TextInput::make('key')
                            ->label('Kunci Pengaturan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($context) => $context === 'edit')
                            ->helperText('Format: office_latitude, office_longitude, office_radius'),

                        // Handle generic value input (string, number, json)
                        Forms\TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->hidden(fn($get) => in_array($get('type'), ['boolean', 'select', 'date', 'time'], true))
                            ->dehydrated(true)
                            ->helperText('Masukkan nilai sesuai tipe data'),

                        // Handle select options (currently used for face_threshold)
                        Forms\Select::make('value')
                            ->label('Nilai')
                            ->required()
                            ->visible(fn($get) => $get('type') === 'select')
                            ->options(fn($get) => $get('key') === 'face_threshold'
                                ? [
                                    '0.6' => '0.6 (Strict / Ketat)',
                                    '0.5' => '0.5 (Standard / Normal)',
                                    '0.4' => '0.4 (Loose / Longgar)',
                                    '0.3' => '0.3 (Very Loose / Sangat Longgar)',
                                    '0.2' => '0.2 (Extremely Loose / Ekstrem Sangat Longgar)',
                                    '0.0' => '0.0 (Face Detection Only)',
                                ]
                                : [])
                            ->helperText(fn($get) => $get('key') === 'face_threshold'
                                ? 'Semakin kecil semakin mudah match. 0.0 hanya butuh deteksi wajah.'
                                : 'Pilih nilai sesuai opsi')
                            ->dehydrated(true),

                        // Handle date picker
                        Forms\DatePicker::make('value')
                            ->label('Nilai (Tanggal)')
                            ->visible(fn($get) => $get('type') === 'date')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->dehydrated(true)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->type === 'date' && $state) {
                                    // Ensure value is passed as Y-m-d string to DatePicker
                                    $component->state($state);
                                }
                            }),

                        // Handle time picker
                        Forms\TimePicker::make('value')
                            ->label('Nilai (Jam)')
                            ->visible(fn($get) => $get('type') === 'time')
                            ->seconds(false)
                            ->displayFormat('H:i')
                            ->dehydrated(true)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->type === 'time' && $state) {
                                    $component->state($state);
                                }
                            }),

                        // Handle boolean toggle
                        Forms\Toggle::make('value_boolean')
                            ->label(fn($state) => $state ? 'Status: Aktif' : 'Status: Non-Aktif')
                            ->visible(fn($get) => $get('type') === 'boolean')
                            ->live()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                // Only hydrate if the type is actually boolean
                                if ($record && $record->type === 'boolean') {
                                    $component->state($record->value === 'true' || $record->value === '1');
                                }
                            })
                            ->dehydrated(false) // Do not save this field directly
                            ->afterStateUpdated(function ($state, $set) {
                                // Sync the boolean state to the 'value' field
                                $set('value', $state ? '1' : '0');
                            })
                            ->onColor('success')
                            ->offColor('danger'),

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
                    ->weight('bold')
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'number') {
                            return number_format((float)$state, 6);
                        }
                        if ($record->type === 'boolean') {
                            return filter_var($state, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif';
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
                    ->tooltip(fn($record) => $record->description),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Data')
                    ->options([
                        'string'  => 'String',
                        'number'  => 'Number',
                        'json'    => 'JSON',
                        'boolean' => 'Boolean',
                        'select'  => 'Select',
                        'date'    => 'Date',
                        'time'    => 'Time',
                    ]),
            ])
            ->actions([
                Action::make('toggle')
                    ->label(fn(Setting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'Non-Aktifkan' : 'Aktifkan')
                    ->icon(fn(Setting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(Setting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'danger' : 'success')
                    ->visible(fn(Setting $record) => $record->type === 'boolean')
                    ->action(function (Setting $record) {
                        $newValue = filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? '0' : '1';
                        $record->update(['value' => $newValue]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
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
            'index'            => Pages\ListSettings::route('/'),
            'create'           => Pages\CreateSetting::route('/create'),
            'edit'             => Pages\EditSetting::route('/{record}/edit'),
            'ramadan-settings' => Pages\RamadanSettings::route('/ramadan-settings'),
        ];
    }
}
