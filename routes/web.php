<?php

use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return redirect()->route('filament.user.auth.login');
})->name('login');

Route::get('/', function () {
    if (Auth::check()) {
        // Redirect based on role
        return Auth::user()->role === 'admin'
            ? redirect('/admin')
            : redirect('/user');
    }
    return redirect()->route('login');
});

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
});
