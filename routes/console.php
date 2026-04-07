<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('backup.schedule.enabled')) {
    $backupSchedule = Schedule::command('backup:create --all-connected');

    if (config('backup.schedule.frequency') === 'weekly') {
        $backupSchedule->weekly()->at(config('backup.schedule.time', '02:00'));
    } else {
        $backupSchedule->dailyAt(config('backup.schedule.time', '02:00'));
    }
}
