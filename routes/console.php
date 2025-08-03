<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Note: The 'peminjaman:escalate' command is not found in the codebase.
// This might be a leftover from a deleted file. I will leave it for now.
Schedule::command('peminjaman:escalate')->hourly();

// Schedule the new performance calculation command to run daily.
Schedule::command('app:calculate-performance-scores')->daily();
