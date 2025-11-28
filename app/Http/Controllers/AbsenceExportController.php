<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                $filename = 'Laporan_Absensi_' . $user->name . '_' . $startDate->format('F_Y') . '.xls';
            }
        } else {
            $filename = 'Laporan_Absensi_' . $startDate->format('F_Y') . '.xls';
        }

        // Default jam masuk kantor senin-jumat: 7.30
        $jamMasukKantor = '07:30:00';

        return response()->streamDownload(function () use ($users, $daysInMonth, $startDate, $month, $year, $jamMasukKantor) {
            echo view('exports.attendance', [
                'users' => $users,
                'daysInMonth' => $daysInMonth,
                'monthName' => $startDate->translatedFormat('F'),
                'year' => $year,
                'startDate' => $startDate,
                'jamMasukKantor' => $jamMasukKantor
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
