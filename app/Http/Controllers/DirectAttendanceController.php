<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DirectAttendanceController extends Controller
{
    protected $geoService;

    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::validate($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $today = now()->toDateString();

        $absence = Absence::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$absence) {
            return response()->json([
                'status' => 'check-in',
                'message' => 'Anda akan melakukan Absen Masuk.',
                'user_name' => $user->name,
            ]);
        } elseif ($absence->jam_masuk && !$absence->jam_pulang) {
            return response()->json([
                'status' => 'check-out',
                'message' => 'Anda sudah Absen Masuk pada ' . $absence->jam_masuk->format('H:i') . '. Apakah Anda ingin Absen Pulang sekarang?',
                'user_name' => $user->name,
                'jam_masuk' => $absence->jam_masuk->format('H:i'),
            ]);
        } elseif (!$absence->jam_masuk) {
            return response()->json([
                'status' => 'check-in',
                'message' => 'Anda akan melakukan Absen Masuk.',
                'user_name' => $user->name,
            ]);
        } else {
            return response()->json([
                'status' => 'completed',
                'message' => 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).',
                'user_name' => $user->name,
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'device_token' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check Device Validation Setting
            $setting = Setting::where('key', 'device_validation_enabled')->first();
            $isDeviceValidationEnabled = $setting ? filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) : true;

            if ($isDeviceValidationEnabled) {
                // Strict Mode: Must have registered device and match
                if (!$user->registered_device_id) {
                    // If no device registered yet, register it automatically (First time setup)
                    $user->update(['registered_device_id' => $request->device_token]);
                } elseif ($user->registered_device_id !== $request->device_token) {
                    Auth::logout();
                    return redirect()->back()->with('fraud_alert', 'Hayolohhh mau titip absen siapaaa?, gw laporin lohhh');
                }
            } else {
                // Loose Mode: Auto-register if empty
                if (!$user->registered_device_id) {
                    $user->update(['registered_device_id' => $request->device_token]);
                }
            }

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
            $newDeviceId = null;

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

                // Rotate Device ID for Security
                $newDeviceId = (string) Str::uuid();
                $user->update(['registered_device_id' => $newDeviceId]);

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

            $redirect = redirect()->back()->with($status, $message);

            if ($newDeviceId) {
                $redirect->with('new_device_id', $newDeviceId);
            }

            return $redirect;
        }

        return redirect()->back()->withErrors(['email' => 'Email atau password salah.']);
    }
}
