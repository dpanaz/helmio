<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(
    'helmio:send-monthly-audits',
)
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

    Schedule::command(
    'helmio:dispatch-brokerage-syncs',
)
    ->hourly()
    ->withoutOverlapping();
    
    
    Schedule::command(
    'helmio:generate-monthly-reviews',
)
    ->monthlyOn(1, '08:00')
    ->timezone('America/Chicago')
    ->withoutOverlapping();

    Schedule::command('helmio:generate-valuations')
    ->dailyAt('23:55')
    ->withoutOverlapping()
    ->onOneServer();

    Schedule::command(
    'advisor-audit:dispatch-scheduled'
)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
