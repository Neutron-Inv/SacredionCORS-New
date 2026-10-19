<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS scheduled tasks
    |--------------------------------------------------------------------------
    |
    | These run when the server cron executes: php artisan schedule:run
    | (typically every minute via crontab / cPanel).
    |
    | Cron examples: every 2 hours = 0 0,2,4,6,8,10,12,14,16,18,20,22 * * *
    |                every hour     = 0 * * * *
    |                daily 3am     = 0 3 * * *
    |
    | Station slugs match the database (lagos, abuja, portharcourt, etc.).
    | Per-station jobs are in addition to sync_all — safe to overlap because
    | sync skips files that are already current.
    |
    */

    'schedule' => [

        // Sync every active station. Set to null to disable.
        'sync_all' => env('CORS_SCHEDULE_SYNC_ALL', '0 */2 * * *'),

        // Sync individual stations on their own schedule.
        'sync_stations' => [
            // 'lagos' => '0 * * * *',
            // 'abuja' => '15 * * * *',
            // 'portharcourt' => '30 */2 * * *',
        ],

        // Scan local storage and register files already on disk. Set to null to disable.
        'index' => env('CORS_SCHEDULE_INDEX', '0 3 * * *'),

    ],

    /*
    |--------------------------------------------------------------------------
    | 24-hour merged RINEX orders
    |--------------------------------------------------------------------------
    |
    | Price is in major currency units (5000 NGN = ₦5,000). Paystack is charged
    | in minor units. Today is decided in this timezone so a day still being
    | recorded cannot be ordered. Download links are deleted after download_days.
    |
    */

    'daily' => [
        'price' => (float) env('CORS_24H_PRICE', 5000),
        'currency' => env('CORS_24H_CURRENCY', 'NGN'),
        'download_days' => (int) env('CORS_24H_DOWNLOAD_DAYS', 7),
        'timezone' => env('CORS_24H_TIMEZONE', 'Africa/Lagos'),
        // Keep one observation epoch every N seconds. 0 or 1 leaves the recorded interval unchanged.
        'interval' => max(0, (int) env('CORS_24H_INTERVAL', 30)),
        // Matched as a suffix of the file extension. "o" shortens .26o and leaves .26n, .26g, and the rest intact.
        'shorten_extensions' => array_values(array_filter(array_map(
            static fn (string $extension): string => strtolower(ltrim(trim($extension), '.')),
            explode(',', (string) env('CORS_24H_SHORTEN_EXTENSIONS', 'o')),
        ))),
    ],

];
