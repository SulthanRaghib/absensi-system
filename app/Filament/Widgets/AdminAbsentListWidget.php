<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AdminAbsentListWidget extends Widget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    /** @var view-string */
    protected string $view = 'filament.widgets.admin-absent-list';

    // half width in 2-column dashboard grid
    protected int | string | array $columnSpan = 1;

    public function getAbsentRecords(): Collection
    {
        $today = now()->toDateString();

        $records = User::where('role', 'user')
            ->whereDoesntHave('absences', function ($query) use ($today) {
                $query->whereDate('tanggal', $today)->whereNotNull('jam_masuk');
            })
            ->get()
            ->map(fn($u) => (object) [
                'name'   => $u->name,
                'email'  => $u->email,
                'avatar' => $u->avatar_url ? Storage::url($u->avatar_url) : null,
            ]);

        return $records->values();
    }
}
