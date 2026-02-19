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
use UnitEnum;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Daftar Kehadiran';
    protected static UnitEnum|string|null $navigationGroup = 'Absen & Perizinan';
    protected static ?int $navigationSort = 0;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── 1. IDENTITAS ──────────────────────────────────────────
                Schemas\Section::make('Identitas Kehadiran')
                    ->description('Pilih pengguna dan tanggal absensi yang akan dicatat.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\DatePicker::make('tanggal')
                            ->label('Tanggal Absensi')
                            ->required()
                            ->default(today())
                            ->maxDate(today())
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                if (! $state) {
                                    return;
                                }
                                $service  = new \App\Services\AttendanceService();
                                $schedule = $service->getScheduleForDate(\Carbon\Carbon::parse($state));
                                $set('is_ramadan',         $schedule['is_ramadan']);
                                $set('schedule_jam_masuk', $schedule['jam_masuk']);
                            }),
                    ])
                    ->columns(2),

                // ── 2. JADWAL HARIAN (RAMADAN-AWARE SMART CARD) ──────────
                Schemas\Section::make('Jadwal Harian')
                    ->description('Jadwal yang berlaku pada tanggal absen ini. Diisi otomatis saat tanggal dipilih — dapat diubah manual bila perlu.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        // Full-width toggle at the top
                        Forms\Toggle::make('is_ramadan')
                            ->label('Jadwal Ramadan 🌙')
                            ->helperText('Sistem mengisi ini otomatis. Ubah hanya jika perlu koreksi manual (misalnya mengedit absen di hari Ramadan lalu).')
                            ->live()
                            ->afterStateUpdated(function (bool $state, callable $set, callable $get): void {
                                if ($state) {
                                    // Toggled ON → load Ramadan jam_masuk from settings
                                    $ramadan = \App\Models\Setting::getRamadanSettings();
                                    if (! empty($ramadan['jam_masuk'])) {
                                        $set('schedule_jam_masuk', $ramadan['jam_masuk']);
                                    }
                                } else {
                                    // Toggled OFF → load normal default jam_masuk
                                    $normal = \App\Models\Setting::getDefaultSchedule();
                                    $set('schedule_jam_masuk', $normal['jam_masuk'] ?? '07:30');
                                }
                            })
                            ->inline()
                            ->default(false)
                            ->columnSpanFull(),

                        // Left: editable threshold field
                        Forms\TimePicker::make('schedule_jam_masuk')
                            ->label('Batas Jam Masuk (threshold)')
                            ->helperText('Diisi otomatis berdasarkan tanggal & jadwal Ramadan. Dapat diubah untuk koreksi.')
                            ->seconds(false)
                            ->default('07:30')
                            ->columnSpan(1),

                        // Right: live info banner
                        Forms\Placeholder::make('schedule_info_card')
                            ->label('Status Jadwal')
                            ->content(function (callable $get): \Illuminate\Support\HtmlString {
                                $isRamadan = (bool) $get('is_ramadan');
                                $threshold = $get('schedule_jam_masuk') ?: '07:30';

                                if ($isRamadan) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;'
                                            . 'background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;color:#92400e;">'
                                            . '<span style="font-size:1.4rem;line-height:1;">🌙</span>'
                                            . '<div style="font-size:0.875rem;line-height:1.5;">'
                                            . '<strong>Jadwal Ramadan aktif</strong><br>'
                                            . 'Tepat waktu jika masuk ≤ <strong>' . e($threshold) . '</strong>'
                                            . '</div></div>'
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;'
                                        . 'background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;color:#374151;">'
                                        . '<span style="font-size:1.4rem;line-height:1;">📅</span>'
                                        . '<div style="font-size:0.875rem;line-height:1.5;">'
                                        . '<strong>Jadwal Normal</strong><br>'
                                        . 'Tepat waktu jika masuk ≤ <strong>' . e($threshold) . '</strong>'
                                        . '</div></div>'
                                );
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                // ── 3. WAKTU ABSENSI ──────────────────────────────────────
                Schemas\Section::make('Waktu Absensi')
                    ->description('Isi jam masuk dan jam pulang karyawan.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\TimePicker::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->seconds(false)
                            ->suffixIcon('heroicon-o-arrow-right-circle'),

                        Forms\TimePicker::make('jam_pulang')
                            ->label('Jam Pulang')
                            ->seconds(false)
                            ->suffixIcon('heroicon-o-arrow-left-circle'),
                    ])
                    ->columns(2),

                // ── 4. FOTO ───────────────────────────────────────────────
                Schemas\Section::make('Preview Foto')
                    ->schema([
                        Forms\Placeholder::make('capture_preview')
                            ->label('Foto Absensi')
                            ->content(fn($get) => $get('capture_image')
                                ? "<div class='w-full flex justify-center'><img src='" . asset('storage/' . $get('capture_image')) . "' alt='Foto Absensi' class='max-h-[360px] rounded-lg shadow-lg object-contain' /></div>"
                                : "<div class='text-sm text-gray-400 italic py-4 text-center'>Tidak ada foto</div>")
                            ->html(),
                    ])
                    ->columns(1),

                // ── 5. LOKASI MASUK (collapsed) ───────────────────────────
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

                // ── 6. LOKASI PULANG (collapsed) ──────────────────────────
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

                Tables\Columns\ImageColumn::make('capture_image')
                    ->label('Foto')
                    ->disk('public')
                    // Show only existence check first, or use a smaller conversion if available
                    // For now, limiting height prevents layout shifts
                    ->height(40)
                    ->checkFileExistence(false) // Saves I/O on shared hosting
                    ->circular(),

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
                    ->color(function (Absence $record): string {
                        if (! $record->jam_masuk) {
                            return 'gray';
                        }
                        // Use the threshold that was active at check-in time (Ramadan-aware).
                        // Falls back to 07:30 for records created before this feature.
                        $threshold = $record->schedule_jam_masuk ?? '07:30';
                        return $record->jam_masuk->format('H:i') > $threshold ? 'danger' : 'success';
                    })
                    ->tooltip(function (Absence $record): ?string {
                        if (! $record->jam_masuk || ! $record->schedule_jam_masuk) return null;
                        $onTime = $record->jam_masuk->format('H:i') <= $record->schedule_jam_masuk;
                        return ($onTime ? 'Tepat Waktu' : 'Terlambat') . ' | Batas: ' . $record->schedule_jam_masuk;
                    }),

                Tables\Columns\TextColumn::make('is_ramadan_label')
                    ->label('Jadwal')
                    ->badge()
                    ->getStateUsing(fn(Absence $record): string => $record->is_ramadan ? '🌙 Ramadan' : 'Normal')
                    ->color(fn(Absence $record): string => $record->is_ramadan ? 'warning' : 'gray')
                    ->tooltip(fn(Absence $record): string => $record->is_ramadan
                        ? 'Absen dicatat saat jadwal Ramadan aktif (batas: ' . ($record->schedule_jam_masuk ?? '-') . ')'
                        : 'Absen dicatat saat jadwal hari biasa (batas: ' . ($record->schedule_jam_masuk ?? '07:30') . ')'),

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

                Tables\Columns\TextColumn::make('risk_level')
                    ->label('Risk Level')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'safe' => 'Safe',
                        'warning' => 'Device Dipinjamkan',
                        'danger' => 'Device Bergantian/Joki',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'safe' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        default => 'gray',
                    })
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
                Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Lihat Absensi'),
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
        ];
    }
}
