<?php

use App\Http\Controllers\AbsenceExportController;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing / login choice
Route::get('/', function () {
    if (Auth::check()) {
        // Redirect based on role
        return Auth::user()->role === 'admin'
            ? redirect('/admin')
            : redirect('/user');
    }

    // Show a simple choice page that emphasizes User login but provides Admin login too
    return view('auth.choice');
})->name('home');

// Keep a named `login` route for compatibility and point it to the same choice page
Route::get('/login', function () {
    return view('auth.choice');
})->name('login');

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
