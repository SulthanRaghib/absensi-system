<?php

namespace App\Filament\User\Pages;

use BackedEnum;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use UnitEnum;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static UnitEnum|string|null $navigationGroup = 'Setting';
    protected string $view = 'filament.user.pages.profile';
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $title = 'Edit Profil';
    protected static ?string $slug = 'profile';
    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    // Custom property for the smart avatar upload
    public $newAvatar;

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'jabatan_id' => $user->jabatan_id,
            'unit_kerja_id' => $user->unit_kerja_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pribadi')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Nama Lengkap'),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email Address'),
                    ]),

                Section::make('Informasi Pekerjaan')
                    ->schema([
                        Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->options(Jabatan::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Select::make('unit_kerja_id')
                            ->label('Unit Kerja')
                            ->options(UnitKerja::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Keamanan')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->requiredWith('password')
                            ->same('password')
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // Handle Avatar Upload
        if ($this->newAvatar instanceof TemporaryUploadedFile) {
            // Delete old avatar if exists
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            $path = $this->newAvatar->store('avatars', 'public');
            $user->avatar_url = $path;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->jabatan_id = $data['jabatan_id'];
        $user->unit_kerja_id = $data['unit_kerja_id'];

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        Notification::make()
            ->success()
            ->title('Profil berhasil diperbarui')
            ->send();

        // Reset new avatar state
        $this->newAvatar = null;

        $this->redirect('/user');
    }

    public function deleteAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->avatar_url = null;
        $user->save();

        $this->newAvatar = null;

        Notification::make()
            ->success()
            ->title('Foto profil berhasil dihapus')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }
}
