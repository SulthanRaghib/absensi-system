<?php

namespace App\Filament\User\Pages;

use App\Models\Absence;
use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Absensi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected string $view = 'filament.user.pages.absensi';

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $title = 'Absensi';

    protected static ?int $navigationSort = 0;

    protected function getViewData(): array
    {
        $user = Auth::user();
        $todayAbsence = Absence::getTodayAbsence($user->id);
        $officeLocation = Setting::getOfficeLocation();

        $faceSetting = Setting::where('key', 'face_recognition_enabled')->first();
        $faceRecognitionEnabled = $faceSetting ? filter_var($faceSetting->value, FILTER_VALIDATE_BOOLEAN) : false;

        $thresholdSetting = Setting::where('key', 'face_threshold')->first();
        $faceThreshold = $thresholdSetting ? (float) $thresholdSetting->value : 0.5;

        return [
            'user' => $user,
            'todayAbsence' => $todayAbsence,
            'officeLocation' => $officeLocation,
            'faceRecognitionEnabled' => $faceRecognitionEnabled,
            'faceThreshold' => $faceThreshold,
        ];
    }
}
