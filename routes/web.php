<?php

use App\Http\Controllers\AbsenceExportController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DirectAttendanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Models\Setting;

// Landing / login choice
Route::get('/', function () {
    if (Auth::check()) {
        // Redirect based on role
        return Auth::user()->role === 'admin'
            ? redirect('/admin')
            : redirect('/user');
    }

    $officeLocation = Setting::getOfficeLocation();

    // Show a simple choice page that emphasizes User login but provides Admin login too
    return view('auth.choice', compact('officeLocation'));
})->name('home');

// Keep a named `login` route for compatibility and point it to the same choice page
Route::get('/login', function () {
    $officeLocation = Setting::getOfficeLocation();
    return view('auth.choice', compact('officeLocation'));
})->name('login');

Route::post('/attendance/direct', [DirectAttendanceController::class, 'store'])->name('attendance.direct');
Route::post('/attendance/check-status', [DirectAttendanceController::class, 'checkStatus'])->name('attendance.check-status');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return redirect('/user');
    })->name('dashboard');

    // Absensi Routes
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/check-in', [AbsensiController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [AbsensiController::class, 'checkOut'])->name('check-out');
        Route::get('/office-location', [AbsensiController::class, 'getOfficeLocation'])->name('office-location');
    });

    // Export Route
    Route::get('/custom-exports/absences/monthly', [AbsenceExportController::class, 'export'])->name('absences.export-monthly');
});
