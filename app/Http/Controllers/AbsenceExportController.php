<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AbsenceExportController extends Controller
{
    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $userId = $request->input('user_id');

        $startDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;

        // Get all users except admins if needed, or all users
        $query = User::where('role', '!=', 'admin');

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->with(['jabatan', 'absences' => function ($query) use ($month, $year) {
            $query->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year);
        }])
            ->orderBy('name')
            ->get();
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $filename = 'Laporan_Absensi_' . $user->name . '_' . $startDate->format('F_Y') . '.xlsx';
            }
        } else {
            $filename = 'Laporan_Absensi_' . $startDate->format('F_Y') . '.xlsx';
        }

        // Default jam masuk kantor senin-jumat: 7.30
        $jamMasukKantor = '07:30:00';

        return Excel::download(new AttendanceExport($users, $daysInMonth, $startDate->translatedFormat('F'), $year, $startDate, $jamMasukKantor), $filename);
    }
}
