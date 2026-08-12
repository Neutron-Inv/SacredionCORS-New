<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$corsSchedule = config('cors.schedule', []);

if ($cron = $corsSchedule['sync_all'] ?? null) {
    Schedule::command('cors:sync --log')->cron($cron);
}

foreach ($corsSchedule['sync_stations'] ?? [] as $slug => $cron) {
    if (! $cron) {
        continue;
    }

    Schedule::command("cors:sync {$slug} --log")->cron($cron);
}

if ($cron = $corsSchedule['index'] ?? null) {
    Schedule::command('cors:index')->cron($cron);
}
