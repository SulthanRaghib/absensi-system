<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the holiday sync to run monthly
use Illuminate\Support\Facades\Schedule;

// Run on the last day of the month at 23:00 (11 PM)
// Ensuring data is ready for the 1st of the next month.
Schedule::command('app:sync-holidays')
    ->lastDayOfMonth('23:00')
    ->description('Sync national holidays from API for upcoming month');
