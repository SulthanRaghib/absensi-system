<?php

namespace App\Filament\User\Resources\MyAbsences;

use App\Filament\User\Resources\MyAbsences\Pages;
use App\Models\Absence;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components as Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MyAbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Riwayat Absensi';
    protected static UnitEnum|string|null $navigationGroup = 'Absen & Riwayat';

    protected static ?int $navigationSort = 1;

    // Hanya tampilkan data user yang login
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function canCreate(): bool
    {
        return false; // User tidak bisa create manual
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Section::make('Detail Absensi')
                    ->schema([
                        Forms\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->disabled(),

                        Forms\TimePicker::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->disabled(),

                        Forms\TimePicker::make('jam_pulang')
                            ->label('Jam Pulang')
                            ->disabled(),
                    ])
                    ->columns(3),

                // Large preview photo for modern, eye-catching UX
                Schemas\Section::make('Preview Foto')
                    ->schema([
                        Forms\Placeholder::make('capture_preview')
                            ->label('Foto Cek In')
                            ->content(fn($get) => $get('capture_image') ? "<div class='w-full flex justify-center'><img src='" . asset('storage/' . $get('capture_image')) . "' alt='Foto Absensi' class='max-h-[360px] rounded-lg shadow-lg object-contain' /></div>" : "<div class='text-sm text-gray-500'>Tidak ada foto</div>")
                            ->html(),
                    ])
                    ->columns(1),

                Schemas\Section::make('Lokasi Check In')
                    ->schema([
                        Forms\TextInput::make('lat_masuk')
                            ->label('Latitude')
                            ->disabled(),

                        Forms\TextInput::make('lng_masuk')
                            ->label('Longitude')
                            ->disabled(),

                        Forms\TextInput::make('distance_masuk')
                            ->label('Jarak (meter)')
                            ->disabled()
                            ->suffix('m'),
                    ])
                    ->columns(3),

                Schemas\Section::make('Lokasi Check Out')
                    ->schema([
                        Forms\TextInput::make('lat_pulang')
                            ->label('Latitude')
                            ->disabled(),

                        Forms\TextInput::make('lng_pulang')
                            ->label('Longitude')
                            ->disabled(),

                        Forms\TextInput::make('distance_pulang')
                            ->label('Jarak (meter)')
                            ->disabled()
                            ->suffix('m'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ImageColumn::make('capture_image')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i:s')
                    ->placeholder('-')
                    ->badge()
                    ->color(function (Absence $record): string {
                        if (! $record->jam_masuk) return 'gray';
                        $threshold = $record->schedule_jam_masuk ?? '07:30';
                        return $record->jam_masuk->format('H:i') > $threshold ? 'danger' : 'success';
                    })
                    ->tooltip(function (Absence $record): ?string {
                        if (! $record->jam_masuk) return null;
                        $threshold  = $record->schedule_jam_masuk ?? '07:30';
                        $onTime     = $record->jam_masuk->format('H:i') <= $threshold;
                        $ramadanTag = $record->is_ramadan ? ' (Ramadan)' : '';
                        return ($onTime ? '✅ Tepat Waktu' : '⚠️ Terlambat') . ' | Batas: ' . $threshold . $ramadanTag;
                    }),

                Tables\Columns\TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->time('H:i:s')
                    ->placeholder('-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('jadwal')
                    ->label('Jadwal')
                    ->badge()
                    ->getStateUsing(fn(Absence $record): string => $record->is_ramadan ? '🌙 Ramadan' : 'Normal')
                    ->color(fn(Absence $record): string => $record->is_ramadan ? 'warning' : 'gray')
                    ->tooltip(fn(Absence $record): string => $record->is_ramadan
                        ? 'Dicatat saat jadwal Ramadan aktif. Batas masuk: ' . ($record->schedule_jam_masuk ?? '-')
                        : 'Dicatat saat jadwal hari biasa. Batas masuk: ' . ($record->schedule_jam_masuk ?? '07:30')),

                Tables\Columns\TextColumn::make('distance_masuk')
                    ->label('Jarak Masuk')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' m' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('distance_pulang')
                    ->label('Jarak Pulang')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' m' : '-')
                    ->sortable(),

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
                Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(fn(Builder $query) => $query->whereMonth('tanggal', now()->month))
                    ->default(),
                Filter::make('from_date')
                    ->label('Dari Tanggal')
                    ->form([
                        Forms\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['from'],
                            fn(Builder $query, $date) => $query->whereDate('tanggal', '>=', $date),
                        );
                    }),
                Filter::make('to_date')
                    ->label('Sampai Tanggal')
                    ->form([
                        Forms\DatePicker::make('to')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['to'],
                            fn(Builder $query, $date) => $query->whereDate('tanggal', '<=', $date),
                        );
                    }),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Lihat Absensi')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyAbsences::route('/'),
        ];
    }
}
