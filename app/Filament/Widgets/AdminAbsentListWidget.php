<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AdminAbsentListWidget extends Widget
{
    protected static ?int $sort = 5;

    protected ?string $pollingInterval = null;

    /** @var view-string */
    protected string $view = 'filament.widgets.admin-absent-list';

    // full width
    protected int | string | array $columnSpan = 6;

    public function getAbsentRecords(): Collection
    {
        $today = now()->toDateString();

        $records = User::where('role', 'user')
            ->whereDoesntHave('absences', function ($query) use ($today) {
                $query->whereDate('tanggal', $today)->whereNotNull('jam_masuk');
            })
            ->get()
            ->map(fn($u) => (object) [
                'name' => $u->name,
                'email' => $u->email,
            ]);

        return $records->values();
    }
}
