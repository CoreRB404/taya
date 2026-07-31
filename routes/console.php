<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('taya:flag-overdue-phases')->dailyAt('00:00');
Schedule::command('taya:recompute-overstay')->dailyAt('01:00');
