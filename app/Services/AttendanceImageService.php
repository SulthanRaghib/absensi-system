<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceImageService
{
    /**
     * Store a base64 PNG image in the public disk and return the stored path.
     *
     * This preserves the current behavior:
     * - strips the PNG data URI prefix if present
     * - replaces spaces with plus signs
     * - stores into `absensi_photos/`
     */
    public function storePng(string $base64Image, string $directory = 'absensi_photos'): string
    {
        $image = str_replace('data:image/png;base64,', '', $base64Image);
        $image = str_replace(' ', '+', $image);
        $imageName = $directory . '/' . Str::random(10) . '.png';

        Storage::disk('public')->put($imageName, base64_decode($image));

        return $imageName;
    }
}
