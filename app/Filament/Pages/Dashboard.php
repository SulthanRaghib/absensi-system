<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getColumns(): int | string | array
    {
        return [
            'default' => 3, // 3 columns for better widget layout
            'sm' => 1,      // 1 column on small screens
            'md' => 2,      // 2 columns on medium screens
            'lg' => 3,      // 3 columns on large screens
            'xl' => 3,      // 3 columns on extra large screens
        ];
    }
}
