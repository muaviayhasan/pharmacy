<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly maintenance: flag expired stock, then regenerate alerts.
Schedule::command('inventory:expiry-sweep')->dailyAt('01:00');
Schedule::command('alerts:scan')->dailyAt('02:00');
