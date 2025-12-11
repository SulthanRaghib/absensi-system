<?php

namespace App\Filament\Resources\Absences;

use App\Filament\Resources\Absences\Pages;
use App\Models\Absence;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components as Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Daftar Kehadiran';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Section::make('Detail Kehadiran')
                    ->schema([
                        Forms\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Forms\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(today()),

                        Forms\TimePicker::make('jam_masuk')
                            ->label('Jam Masuk'),

                        Forms\TimePicker::make('jam_pulang')
                            ->label('Jam Pulang'),
                    ])
                    ->columns(2),

                Schemas\Section::make('Lokasi Masuk')
                    ->schema([
                        Forms\TextInput::make('lat_masuk')
                            ->label('Latitude Masuk')
                            ->numeric(),

                        Forms\TextInput::make('lng_masuk')
                            ->label('Longitude Masuk')
                            ->numeric(),

                        Forms\TextInput::make('distance_masuk')
                            ->label('Jarak Masuk (meter)')
                            ->numeric()
                            ->suffix('m'),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Schemas\Section::make('Lokasi Pulang')
                    ->schema([
                        Forms\TextInput::make('lat_pulang')
                            ->label('Latitude Pulang')
                            ->numeric(),

                        Forms\TextInput::make('lng_pulang')
                            ->label('Longitude Pulang')
                            ->numeric(),

                        Forms\TextInput::make('distance_pulang')
                            ->label('Jarak Pulang (meter)')
                            ->numeric()
                            ->suffix('m'),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Forms\Textarea::make('device_info')
                    ->label('Info Perangkat')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-')
                    ->badge()
                    ->color(fn($state) => \Carbon\Carbon::parse($state)->format('H:i') > '07:30' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i')
                    ->placeholder('-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('distance_masuk')
                    ->label('Jarak Masuk')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' m' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('distance_pulang')
                    ->label('Jarak Pulang')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' m' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('device_info')
                    ->label('Device Info')
                    ->limit(50)
                    ->tooltip(fn(Absence $record): string => $record->device_info ?? '')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.registered_device_id')
                    ->label('Device ID')
                    ->limit(20)
                    ->copyable()
                    ->copyableState(fn(Absence $record): string => $record->user->registered_device_id ?? '')
                    ->tooltip(fn(Absence $record): string => $record->user->registered_device_id ?? '')
                    ->searchable()
                    ->color(function ($state, $record) {
                        if (empty($state)) return null;

                        $devices = json_decode($state, true);
                        if (!is_array($devices)) {
                            $devices = [$state];
                        }

                        foreach ($devices as $device) {
                            // Check if this device ID exists in any other user's registered_device_id
                            $exists = Absence::where('user_id', '!=', $record->user_id)
                                ->whereHas('user', function (Builder $query) use ($device) {
                                    $query->where('registered_device_id', 'like', '%' . $device . '%');
                                })
                                ->exists();

                            if ($exists) {
                                return 'danger';
                            }
                        }

                        return null;
                    }),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (Absence $record) {
                        if ($record->jam_pulang) return 'complete';
                        if ($record->jam_masuk) return 'partial';
                        return 'incomplete';
                    })
                    ->icons([
                        'heroicon-o-check-circle' => 'complete',
                        'heroicon-o-clock' => 'partial',
                        'heroicon-o-x-circle' => 'incomplete',
                    ])
                    ->colors([
                        'success' => 'complete',
                        'warning' => 'partial',
                        'danger' => 'incomplete',
                    ]),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable(),

                Filter::make('tanggal')
                    ->form([
                        Forms\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->default(today()),
                        Forms\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->default(today()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'], fn($query, $date) => $query->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai'], fn($query, $date) => $query->whereDate('tanggal', '<=', $date));
                    }),

                SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'complete' => 'Lengkap (Masuk & Pulang)',
                        'partial' => 'Masuk Saja',
                        'incomplete' => 'Belum Absen',
                    ])
                    ->query(function (Builder $query, $state): Builder {
                        return match ($state['value'] ?? null) {
                            'complete' => $query->whereNotNull('jam_masuk')->whereNotNull('jam_pulang'),
                            'partial' => $query->whereNotNull('jam_masuk')->whereNull('jam_pulang'),
                            'incomplete' => $query->whereNull('jam_masuk'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
            'view' => Pages\ViewAbsence::route('/{record}'),
        ];
    }
}
