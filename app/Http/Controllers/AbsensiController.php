<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class AbsensiController extends Controller
{
    protected $geoService;

    public function __construct(GeoLocationService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Show absensi page
     */
    public function index()
    {
        $user = Auth::user();
        $todayAbsence = Absence::getTodayAbsence($user->id);
        $officeLocation = Setting::getOfficeLocation();

        return view('absensi.index', compact('user', 'todayAbsence', 'officeLocation'));
    }

    /**
     * Store check-in (Absen Masuk)
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'device_token' => 'required|string',
        ]);

        $user = Auth::user();

        // Device Binding Logic
        if (!$user->registered_device_id) {
            $user->update(['registered_device_id' => $validated['device_token']]);
        } elseif ($user->registered_device_id !== $validated['device_token']) {
            return response()->json([
                'success' => false,
                'message' => 'Device mismatch detected.',
                'cheat_alert' => true
            ], 403);
        }

        // Check if already checked in today
        if (Absence::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen masuk hari ini.',
            ], 400);
        }

        // Validate GPS accuracy
        $accuracyCheck = $this->geoService->validateAccuracy($validated['accuracy']);
        if (!$accuracyCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $accuracyCheck['message'],
            ], 400);
        }

        // Validate location
        $locationCheck = $this->geoService->validateLocation(
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$locationCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $locationCheck['message'],
                'distance' => $locationCheck['distance'],
            ], 400);
        }

        // Get device info
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $version = $agent->version($browser);

        $deviceType = 'Unknown';
        if ($agent->isDesktop()) {
            $deviceType = 'Desktop';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } elseif ($agent->isPhone()) {
            $deviceType = 'Phone';
        } elseif ($agent->isRobot()) {
            $deviceType = 'Robot';
        }

        $info = "{$platform} | {$browser} {$version} | {$device} ({$deviceType})";

        // Add IP address
        $ip = $request->ip();
        $info .= " | IP: {$ip}";

        // Create absence record
        $absence = Absence::create([
            'user_id' => $user->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'lat_masuk' => $validated['latitude'],
            'lng_masuk' => $validated['longitude'],
            'distance_masuk' => $locationCheck['distance'],
            'device_info' => $info,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil! ' . $locationCheck['message'],
            'data' => [
                'jam_masuk' => $absence->jam_masuk->format('H:i:s'),
                'distance' => $locationCheck['distance'],
            ],
        ]);
    }

    /**
     * Store check-out (Absen Pulang)
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        // Check if not checked in yet
        if (!Absence::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk hari ini.',
            ], 400);
        }

        // Check if already checked out
        if (Absence::hasCheckedOutToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen pulang hari ini.',
            ], 400);
        }

        // Validate GPS accuracy
        $accuracyCheck = $this->geoService->validateAccuracy($validated['accuracy']);
        if (!$accuracyCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $accuracyCheck['message'],
            ], 400);
        }

        // Validate location
        $locationCheck = $this->geoService->validateLocation(
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$locationCheck['valid']) {
            return response()->json([
                'success' => false,
                'message' => $locationCheck['message'],
                'distance' => $locationCheck['distance'],
            ], 400);
        }

        // Update absence record
        $absence = Absence::getTodayAbsence($user->id);
        $absence->update([
            'jam_pulang' => now(),
            'lat_pulang' => $validated['latitude'],
            'lng_pulang' => $validated['longitude'],
            'distance_pulang' => $locationCheck['distance'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil! ' . $locationCheck['message'],
            'data' => [
                'jam_pulang' => $absence->jam_pulang->format('H:i:s'),
                'distance' => $locationCheck['distance'],
            ],
        ]);
    }

    /**
     * Get office location (for map)
     */
    public function getOfficeLocation()
    {
        return response()->json(Setting::getOfficeLocation());
    }
}
