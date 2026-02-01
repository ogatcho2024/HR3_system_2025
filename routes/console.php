<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Offline ML prediction generation (scheduler)
Schedule::command('ml:generate-predictions --approval-only')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('ml:generate-predictions --demand-only')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();
