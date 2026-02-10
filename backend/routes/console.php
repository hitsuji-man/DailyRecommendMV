<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tokens:purge-expired')
    ->dailyAt('02:55')
    ->withoutOverlapping();

Schedule::command('videos:save-mixed-daily')
    ->dailyAt('03:00')
    ->withoutOverlapping();
