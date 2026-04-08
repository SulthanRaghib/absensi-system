<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Setting;
use App\Services\AttendanceActionService;
use App\Services\AttendanceImageService;
use App\Services\DeviceRiskService;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    protected $geoService;
    protected $attendanceActionService;
    protected $attendanceImageService;
    protected $deviceRiskService;

    public function __construct(
        GeoLocationService $geoService,
        AttendanceActionService $attendanceActionService,
        AttendanceImageService $attendanceImageService,
        DeviceRiskService $deviceRiskService
    ) {
        $this->geoService = $geoService;
        $this->attendanceActionService = $attendanceActionService;
        $this->attendanceImageService = $attendanceImageService;
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

            $imagePath = $this->attendanceImageService->storePng($validated['image']);
        }

        $info = $this->geoService->getDeviceInfo($request);

        $result = $this->attendanceActionService->checkIn(
            $user,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['accuracy'],
            $riskLevel,
            $info,
            $imagePath,
        );

        if (! $result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'distance' => $result['distance'] ?? null,
            ], $result['status']);
        }

        $absence = $result['absence'];
        $schedule = $result['schedule'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'jam_masuk'   => $absence->jam_masuk->format('H:i:s'),
                'status'      => $result['statusLabel'],
                'is_ramadan'  => $schedule['is_ramadan'],
                'jam_threshold' => $schedule['jam_masuk'],
                'distance'    => $result['distance'],
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

        $result = $this->attendanceActionService->checkOut(
            $user,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['accuracy'],
        );

        if (! $result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'distance' => $result['distance'] ?? null,
            ], $result['status']);
        }

        $absence = $result['absence'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'jam_pulang' => $absence->jam_pulang->format('H:i:s'),
                'distance' => $result['distance'],
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
