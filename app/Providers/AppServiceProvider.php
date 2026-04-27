<?php

namespace App\Providers;

use App\Models\AttendanceCorrection;
use App\Models\Notification as AppNotification;
use App\Models\Permission;
use App\Observers\AttendanceCorrectionObserver;
use App\Observers\PermissionObserver;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            LoginResponse::class
        );

        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Permission::observe(PermissionObserver::class);
        AttendanceCorrection::observe(AttendanceCorrectionObserver::class);

        // Prevent physical deletion for pending approval notifications.
        DatabaseNotification::deleting(function (DatabaseNotification $notification): bool {
            $referenceType = $notification->getAttribute('reference_type');
            $status = $notification->getAttribute('status') ?? 'pending';

            if (in_array($referenceType, [AppNotification::REFERENCE_PERMISSION, AppNotification::REFERENCE_ATTENDANCE_CORRECTION], true) && $status === AppNotification::STATUS_PENDING) {
                $notification->setAttribute('is_cleared', true);

                if (! $notification->read_at) {
                    $notification->read_at = now();
                }

                $notification->saveQuietly();

                return false;
            }

            return true;
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
