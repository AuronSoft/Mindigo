<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('news:fetch')
    ->everyThreeHours()
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command('calendar:send-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:sync-providers')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:renew-provider-webhooks')
    ->dailyAt('03:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:cleanup-realtime')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:check-turn')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:prune-data')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('live-sessions:backup')
    ->dailyAt('01:30')
    ->withoutOverlapping()
    ->onOneServer();
