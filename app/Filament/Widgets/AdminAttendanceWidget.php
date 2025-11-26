<?php

// namespace App\Filament\Widgets;

// use App\Models\Absence;
// use App\Models\User;
// use Filament\Widgets\Widget;
// use Illuminate\Support\Carbon;
// use App\Models\Setting;

// class AdminAttendanceWidget extends Widget
// {
//     /** @var view-string */
//     protected string $view = 'filament.widgets.admin-attendance-widget';

//     public function getData(): array
//     {
//         $totalUsers = User::count();

//         // Today's attendance
//         $today = now()->startOfDay();
//         $presentToday = Absence::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count();

//         // Work schedule and grace
//         $workStart = Carbon::createFromTimeString('07:30:00');
//         $graceMinutes = Setting::get('attendance_grace_minutes', 10);

//         // Late list: today records where jam_masuk later than workStart + grace
//         $lateRecords = Absence::with('user')
//             ->whereDate('tanggal', $today)
//             ->whereNotNull('jam_masuk')
//             ->get()
//             ->filter(function ($r) use ($workStart, $graceMinutes) {
//                 if (! $r->jam_masuk) return false;
//                 $jm = Carbon::parse($r->jam_masuk->format('H:i:s'));
//                 return $jm->gt($workStart->copy()->addMinutes($graceMinutes));
//             });

//         $lateCount = $lateRecords->count();
//         $lateNames = $lateRecords->pluck('user.name')->filter()->unique()->values()->all();

//         // Absent count (not present)
//         $absent = max(0, $totalUsers - $presentToday);

//         // Small daily series (last 7 days present counts)
//         $last7 = collect();
//         for ($i = 6; $i >= 0; $i--) {
//             $d = now()->subDays($i)->toDateString();
//             $cnt = Absence::whereDate('tanggal', $d)->whereNotNull('jam_masuk')->count();
//             $last7->push(['date' => $d, 'present' => $cnt]);
//         }

//         return compact('totalUsers', 'presentToday', 'absent', 'lateCount', 'lateNames', 'last7');
//     }
// }
