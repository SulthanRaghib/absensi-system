<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminAttendanceStats;
use App\Filament\Widgets\AdminLateListWidget;
use App\Filament\Widgets\AdminAbsentListWidget;
use App\Filament\Widgets\AdminLast7Chart;
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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->brandLogo(asset('images/Logo_bapeten.png'))
            ->brandLogoHeight('5rem')
            ->brandName('Absensi Maganghub - BAPETEN')
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                AdminAttendanceStats::class,
                AdminLateListWidget::class,
                AdminAbsentListWidget::class,
                AdminLast7Chart::class,
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
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn() => Filament::getCurrentPanel()?->getId() === 'admin' ? view('filament.auth.login-heading')->render() : '')
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn() => Filament::getCurrentPanel()?->getId() === 'admin' ? view('filament.auth.back-button', ['home' => route('home')])->render() : '');
    }
}
