<?php

namespace App\Filament\User\Auth;

use App\Models\Jabatan;
use App\Models\UnitKerja;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pribadi')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        FileUpload::make('avatar_url')
                            ->avatar()
                            ->label('Foto Profil')
                            ->directory('avatars')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '1:1',
                            ]),
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
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    // Redirect to user panel after profile update
    protected function getRedirectUrl(): string
    {
        return filament()->getPanel('user')->getUrl();
    }
}
