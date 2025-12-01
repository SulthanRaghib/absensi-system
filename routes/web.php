<?php

use App\Http\Controllers\AbsenceExportController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectAttendanceController;
use App\Filament\Pages\Auth\InternRegister;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Intern Registration Routes
Route::get('/intern-register/{token}', InternRegister::class)->name('register.intern');

// Redirect root to choice page
Route::get('/', [AuthController::class, 'login'])->name('home');

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
