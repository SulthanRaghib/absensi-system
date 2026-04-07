<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.dashboard';

    public function getColumns(): array|int
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
