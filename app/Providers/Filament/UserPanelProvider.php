<?php

namespace App\Providers\Filament;

use App\Filament\User\Auth\EditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\View\PanelsRenderHook;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Support\Str;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login()
            ->spa()
            ->brandLogo(asset('images/Logo_bapeten.png'))
            ->brandLogoHeight('5rem')
            ->brandName('Absensi Maganghub - BAPETEN')
            // ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\Filament\User\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\Filament\User\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\Filament\User\Widgets')
            ->widgets([
                // AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn() => Filament::getCurrentPanel()?->getId() === 'user' ? view('filament.auth.login-heading')->render() : '')
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn() => Filament::getCurrentPanel()?->getId() === 'user' ? view('filament.auth.back-button', ['home' => route('home')])->render() : '')
            // Load Smart Profile assets early so Alpine can evaluate x-data safely (SPA pages don't re-run inline scripts).
            ->renderHook(PanelsRenderHook::HEAD_END, fn() => Filament::getCurrentPanel()?->getId() === 'user'
                ? view('filament.user.hooks.smart-profile-assets')->render()
                : '');
    }
}
