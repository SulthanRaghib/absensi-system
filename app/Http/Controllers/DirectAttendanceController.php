<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Models\User;
use App\Services\AttendanceActionService;
use App\Services\AttendanceImageService;
use App\Services\DeviceRiskService;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectAttendanceController extends Controller
{
    protected $geoService;
    protected $deviceRiskService;
    protected $attendanceActionService;
    protected $attendanceImageService;

    public function __construct(
        GeoLocationService $geoService,
        DeviceRiskService $deviceRiskService,
        AttendanceActionService $attendanceActionService,
        AttendanceImageService $attendanceImageService
    ) {
        $this->geoService = $geoService;
        $this->deviceRiskService = $deviceRiskService;
        $this->attendanceActionService = $attendanceActionService;
        $this->attendanceImageService = $attendanceImageService;
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

            $riskLevel = 'safe';

            // Face Recognition Logic
            $isFaceRecognitionEnabled = Setting::isFaceRecognitionEnabled();
            $imagePath = null;

            if ($isFaceRecognitionEnabled && $request->filled('image')) {
                $imagePath = $this->attendanceImageService->storePng($request->image);
            }

            $today = now()->toDateString();
            $now = now();

            // Check for existing absence record for today
            $absence = Absence::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->first();

            $message = '';
            $status = 'success';
            $newDeviceId = null;

            if (!$absence || !$absence->jam_masuk) {
                // Check In (new record OR edge-case incomplete record)
                if ($isFaceRecognitionEnabled && !$imagePath) {
                    Auth::logout();
                    return redirect()->back()->with('error', 'Wajah wajib diverifikasi untuk Absen Masuk.');
                }

                $riskLevel = $this->deviceRiskService->assessForCheckIn($user, $request->device_token);
                $result = $this->attendanceActionService->checkIn(
                    $user,
                    (float) $request->latitude,
                    (float) $request->longitude,
                    (float) $request->accuracy,
                    $riskLevel,
                    $this->geoService->getDeviceInfo($request),
                    $imagePath,
                    $now,
                );

                if (! $result['ok']) {
                    Auth::logout();
                    return redirect()->back()->with('error', $result['message']);
                }

                $message = 'Berhasil Absen Masuk! Selamat Bekerja, ' . $user->name;
            } elseif ($absence->jam_masuk && !$absence->jam_pulang) {
                // Check Out
                $result = $this->attendanceActionService->checkOut(
                    $user,
                    (float) $request->latitude,
                    (float) $request->longitude,
                    (float) $request->accuracy,
                    $now,
                );

                if (! $result['ok']) {
                    Auth::logout();
                    return redirect()->back()->with('error', $result['message']);
                }

                $message = 'Berhasil Absen Pulang! Hati-hati di jalan, ' . $user->name;
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
