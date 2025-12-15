<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        $avatarUrl = $user->avatar_url ? asset('storage/' . $user->avatar_url) : null;

        if (!$absence) {
            return response()->json([
                'status' => 'check-in',
                'message' => 'Anda akan melakukan Absen Masuk.',
                'user_name' => $user->name,
                'avatar_url' => $avatarUrl,
            ]);
        } elseif ($absence->jam_masuk && !$absence->jam_pulang) {
            return response()->json([
                'status' => 'check-out',
                'message' => 'Anda sudah Absen Masuk pada ' . $absence->jam_masuk->format('H:i') . '. Apakah Anda ingin Absen Pulang sekarang?',
                'user_name' => $user->name,
                'jam_masuk' => $absence->jam_masuk->format('H:i'),
                'avatar_url' => $avatarUrl,
            ]);
        } elseif (!$absence->jam_masuk) {
            return response()->json([
                'status' => 'check-in',
                'message' => 'Anda akan melakukan Absen Masuk.',
                'user_name' => $user->name,
                'avatar_url' => $avatarUrl,
            ]);
        } else {
            return response()->json([
                'status' => 'completed',
                'message' => 'Anda sudah menyelesaikan absensi hari ini (Masuk & Pulang).',
                'user_name' => $user->name,
                'avatar_url' => $avatarUrl,
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
            'accuracy' => 'required|numeric|min:0',
            'device_token' => 'required|string',
            'image' => 'nullable|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check Device Validation Setting
            $setting = Setting::where('key', 'device_validation_enabled')->first();
            $isDeviceValidationEnabled = $setting ? filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) : true;

            $deviceToken = $request->device_token;
            $riskLevel = 'safe';

            if ($isDeviceValidationEnabled) {
                // Step A: Record Device
                $userDevice = \App\Models\UserDevice::firstOrCreate(
                    ['user_id' => $user->id, 'device_unique_id' => $deviceToken],
                    ['last_used_at' => now()]
                );
                $userDevice->update(['last_used_at' => now()]);

                // Step B: Collision Detection
                $otherUsersWithSameDevice = \App\Models\UserDevice::where('device_unique_id', $deviceToken)
                    ->where('user_id', '!=', $user->id)
                    ->get();

                // Step C: Risk Logic
                if ($otherUsersWithSameDevice->isNotEmpty()) {
                    $riskLevel = 'danger';

                    // Update other users' attendance records
                    foreach ($otherUsersWithSameDevice as $otherDevice) {
                        $otherUserAbsence = Absence::where('user_id', $otherDevice->user_id)
                            ->whereDate('tanggal', today())
                            ->first();

                        if ($otherUserAbsence) {
                            $otherUserAbsence->update(['risk_level' => 'warning']);
                        }
                    }
                }
            } else {
                // Loose Mode: Record history but don't flag risk
                \App\Models\UserDevice::firstOrCreate(
                    ['user_id' => $user->id, 'device_unique_id' => $deviceToken],
                    ['last_used_at' => now()]
                )->update(['last_used_at' => now()]);
            }

            // Face Recognition Logic
            $faceSetting = Setting::where('key', 'face_recognition_enabled')->first();
            $isFaceRecognitionEnabled = $faceSetting ? filter_var($faceSetting->value, FILTER_VALIDATE_BOOLEAN) : false;
            $imagePath = null;

            if ($isFaceRecognitionEnabled && $request->filled('image')) {
                $image = $request->image;
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'absensi_photos/' . Str::random(10) . '.png';
                Storage::disk('public')->put($imageName, base64_decode($image));
                $imagePath = $imageName;
            }

            $today = now()->toDateString();
            $now = now();

            // Validate GPS accuracy
            $accuracyCheck = $this->geoService->validateAccuracy((float) $request->accuracy);
            if (!$accuracyCheck['valid']) {
                Auth::logout();
                return redirect()->back()->with('error', $accuracyCheck['message']);
            }

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
                if ($isFaceRecognitionEnabled && !$imagePath) {
                    Auth::logout();
                    return redirect()->back()->with('error', 'Wajah wajib diverifikasi untuk Absen Masuk.');
                }

                Absence::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'jam_masuk' => $now,
                    'lat_masuk' => $request->latitude,
                    'lng_masuk' => $request->longitude,
                    'distance_masuk' => $locationCheck['distance'],
                    'device_info' => $this->geoService->getDeviceInfo($request),
                    'capture_image' => $imagePath,
                    'risk_level' => $riskLevel,
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

            $redirect = redirect()->back()->with($status, $message);

            return $redirect;
        }

        return redirect()->back()->withErrors(['email' => 'Email atau password salah.']);
    }
}
