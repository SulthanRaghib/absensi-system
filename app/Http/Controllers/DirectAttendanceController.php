<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DirectAttendanceController extends Controller
{
    protected $geoService;

    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $today = now()->toDateString();
            $now = now();

            // Validate location
            $locationCheck = $this->geoService->validateLocation(
                $request->latitude,
                $request->longitude
            );

            if (!$locationCheck['valid']) {
                Auth::logout();
                return redirect()->back()->with('error', $locationCheck['message']);
            }

            // Check for existing absence record for today
            $absence = Absence::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->first();

            $message = '';
            $status = 'success';

            if (!$absence) {
                // Check In
                Absence::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'jam_masuk' => $now,
                    'lat_masuk' => $request->latitude,
                    'lng_masuk' => $request->longitude,
                    'distance_masuk' => $locationCheck['distance'],
                    'device_info' => $this->geoService->getDeviceInfo($request),
                ]);
                $message = 'Berhasil Absen Masuk! Selamat Bekerja, ' . $user->name;
            } elseif ($absence->jam_masuk && !$absence->jam_pulang) {
                // Check Out
                $absence->update([
                    'jam_pulang' => $now,
                    'lat_pulang' => $request->latitude,
                    'lng_pulang' => $request->longitude,
                    'distance_pulang' => $locationCheck['distance'],
                ]);
                $message = 'Berhasil Absen Pulang! Hati-hati di jalan, ' . $user->name;
            } elseif (!$absence->jam_masuk) {
                // Edge case: Record exists but no check-in (maybe created manually without time)
                $absence->update([
                    'jam_masuk' => $now,
                    'lat_masuk' => $request->latitude,
                    'lng_masuk' => $request->longitude,
                    'distance_masuk' => $locationCheck['distance'],
                ]);
                $message = 'Berhasil Absen Masuk! Selamat Bekerja, ' . $user->name;
            } else {
                // Already completed
                $message = 'Anda sudah melakukan absen masuk dan pulang hari ini.';
                $status = 'error';
            }

            // Logout immediately to keep them on the choice page
            Auth::logout();

            // Invalidate session to ensure clean state
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->back()->with($status, $message);
        }

        return redirect()->back()->withErrors(['email' => 'Email atau password salah.']);
    }
}
