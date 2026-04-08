<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Services\AttendanceService;
use App\Services\DeviceRiskService;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    protected $geoService;
    protected $attendanceService;
    protected $deviceRiskService;

    public function __construct(
        GeoLocationService $geoService,
        AttendanceService $attendanceService,
        DeviceRiskService $deviceRiskService
    ) {
        $this->geoService = $geoService;
        $this->attendanceService = $attendanceService;
        $this->deviceRiskService = $deviceRiskService;
    }

    /**
     * Show absensi page
     */
    public function index()
    {
        $user = Auth::user();
        $todayAbsence = Absence::getTodayAbsence($user->id);
        $officeLocation = Setting::getOfficeLocation();
        $faceRecognitionEnabled = Setting::isFaceRecognitionEnabled();

        return view('absensi.index', compact('user', 'todayAbsence', 'officeLocation', 'faceRecognitionEnabled'));
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
            'image' => 'nullable|string',
        ]);

        $user = Auth::user();

        // 1. Device Validation & Risk Assessment (shared service)
        $riskLevel = $this->deviceRiskService->assessForCheckIn($user, $validated['device_token']);

        // 2. Check Face Recognition Setting
        $isFaceRecognitionEnabled = Setting::isFaceRecognitionEnabled();
        $imagePath = null;

        if ($isFaceRecognitionEnabled) {
            if (empty($validated['image'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah wajib diverifikasi (Face Recognition Enabled).',
                ], 400);
            }

            // Decode and save image
            $image = $validated['image'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_photos/' . Str::random(10) . '.png';

            Storage::disk('public')->put($imageName, base64_decode($image));
            $imagePath = $imageName;
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

        $info = $this->geoService->getDeviceInfo($request);

        // Determine late/on-time status using the active schedule (normal or Ramadan)
        $checkInTime = now();
        $schedule    = $this->attendanceService->getTodaySchedule();
        $isLate      = $this->attendanceService->isLate($checkInTime);
        $statusLabel = $isLate ? 'Terlambat' : 'Tepat Waktu';

        // Create absence record
        // `schedule_jam_masuk` and `is_ramadan` snapshot the active schedule so
        // historical reports remain accurate even after Settings are updated.
        $absence = Absence::create([
            'user_id'            => $user->id,
            'tanggal'            => today(),
            'jam_masuk'          => $checkInTime,
            'schedule_jam_masuk' => $schedule['jam_masuk'],    // e.g. '07:30' or '08:00'
            'is_ramadan'         => $schedule['is_ramadan'],   // immutable flag
            'lat_masuk'          => $validated['latitude'],
            'lng_masuk'          => $validated['longitude'],
            'distance_masuk'     => $locationCheck['distance'],
            'device_info'        => $info,
            'capture_image'      => $imagePath,
            'risk_level'         => $riskLevel,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil! ' . $locationCheck['message'],
            'data' => [
                'jam_masuk'   => $absence->jam_masuk->format('H:i:s'),
                'status'      => $statusLabel,
                'is_ramadan'  => $schedule['is_ramadan'],
                'jam_threshold' => $schedule['jam_masuk'],
                'distance'    => $locationCheck['distance'],
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
