<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components as Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\DomCrawler\Form;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['jabatan']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn($context) => $context === 'create')
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255)
                            ->helperText('Kosongkan jika tidak ingin mengubah password'),

                        Forms\Select::make('role')
                            ->label('Role')
                            ->options([
                                'admin' => 'Admin',
                                'user' => 'User',
                            ])
                            ->default('user')
                            ->required(),

                        Forms\Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->relationship('jabatan', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Select::make('unit_kerja_id')
                            ->label('Unit Kerja')
                            ->relationship('unitKerja', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\TextInput::make('registered_device_id')
                            ->label('Registered Device ID')
                            ->maxLength(255)
                            ->helperText('Device ID yang terdaftar untuk validasi absensi pengguna ini'),

                    ])
                    ->columns(2),

                Schemas\Section::make('Avatar')
                    ->schema([
                        Forms\FileUpload::make('avatar_url')
                            ->label('Foto Profil')
                            ->directory('avatars')
                            ->disk('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '1:1',
                            ])
                            ->maxSize(1024)
                            ->helperText('Maksimal ukuran file 1MB.'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'danger' => 'admin',
                        'success' => 'user',
                    ]),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Jabatan')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('registered_device_id')
                    ->label('Device ID')
                    ->limit(20)
                    ->copyableState(fn(User $record): string => $record->registered_device_id ?? '')
                    ->tooltip(fn($record) => $record->registered_device_id)
                    ->color(function ($state, $record) {
                        if (empty($state)) return null;

                        $devices = json_decode($state, true);
                        if (!is_array($devices)) {
                            $devices = [$state];
                        }

                        foreach ($devices as $device) {
                            // Check if this device ID exists in any other user's registered_device_id
                            $exists = User::where('id', '!=', $record->id)
                                ->where('registered_device_id', 'like', '%' . $device . '%')
                                ->exists();

                            if ($exists) {
                                return 'danger';
                            }
                        }

                        return null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
