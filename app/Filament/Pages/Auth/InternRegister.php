<?php

namespace App\Filament\Pages\Auth;

use App\Models\Jabatan;
use App\Models\RegistrationLink;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Forms\Components\Select;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;

#[Layout('filament-panels::components.layout.simple')]
class InternRegister extends BaseRegister
{
    public ?string $token = null;

    public function mount(): void
    {
        // The token is automatically injected by Livewire from the route parameter
        // if the property name matches the route parameter name.
        // However, we also need to validate it.

        $this->token = request()->route('token');

        $link = RegistrationLink::where('token', $this->token)->first();

        if (!$link || !$link->is_active || $link->expires_at->isPast()) {
            abort(403, 'This registration link has expired or is invalid.');
        }

        parent::mount();
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Sign in to your account')
            ->url(route('login'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->options(Jabatan::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'jabatan_id' => $data['jabatan_id'],
        ]);

        return $user;
    }

    public function register(): ?RegistrationResponse
    {
        $response = parent::register();

        if (!$response) {
            return null;
        }

        return new class implements RegistrationResponse {
            public function toResponse($request)
            {
                return redirect()->to('/user');
            }
        };
    }
}
