<?php

namespace App\Console\Commands;

use App\Models\CorsStation;
use App\Services\CorsFtpSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCorsFiles extends Command
{
    protected $signature = 'cors:sync
        {station? : Station slug to sync (omit for all)}
        {--days= : Only look at the last N day folders (default from CORS_SYNC_DAYS)}
        {--full : Walk every yyyy/mm/dd folder within the retention period}
        {--log : Write a detailed log to storage/logs/cors-sync.log}';

    protected $description = 'Sync GNSS RINEX files from FTP into local storage and the database';

    public function handle(CorsFtpSyncService $syncService): int
    {
        $slug = $this->argument('station');
        $days = $this->parseDaysOption();

        if ($days === false) {
            return self::FAILURE;
        }

        $full = (bool) $this->option('full');
        $writeLog = (bool) $this->option('log');

        $stations = CorsStation::query()
            ->where('is_active', true)
            ->where('is_external', false)
            ->whereNotNull('ftp_file_prefix')
            ->when($slug, fn ($query) => $query->where('slug', $slug))
            ->orderBy('sort_order')
            ->get();

        if ($stations->isEmpty()) {
            $this->error($slug ? "No station found for slug [{$slug}]." : 'No syncable stations found.');

            return self::FAILURE;
        }

        if ($writeLog) {
            $syncService->withLogger(function (string $level, string $message) {
                Log::channel('cors')->log($level, $message);

                match ($level) {
                    'error' => $this->error($message),
                    'warning' => $this->warn($message),
                    default => $this->line($message),
                };
            });
        }

        $retentionMonths = (int) config('services.cors_ftp.retention_months', 3);
        $defaultDays = max(1, (int) config('services.cors_ftp.sync_days', 2));
        $window = $full ? 'full retention window' : 'last '.($days ?? $defaultDays).' day(s)';

        $this->comment("Folder mode: {$window} under yyyy/mm/dd (no full-tree walk).");

        if ($retentionMonths > 0) {
            $this->comment("Retention: {$retentionMonths} month(s). Older local files will be deleted.");
        }

        if ($writeLog) {
            $this->comment('Logging to storage/logs/cors-sync.log');
        }

        $totalSynced = 0;
        $totalPruned = 0;
        $totalSkipped = 0;

        foreach ($stations as $station) {
            $this->info("Syncing {$station->name} ({$station->slug})...");

            try {
                $result = $syncService->syncStation($station, $days, $full);
                $totalSynced += $result['synced'];
                $totalPruned += $result['pruned'];
                $totalSkipped += $result['skipped'];
                $this->line("  → {$result['synced']} downloaded, {$result['skipped']} already current, {$result['pruned']} expired removed.");
            } catch (\Throwable $e) {
                $this->error("  → Failed: {$e->getMessage()}");
                Log::channel($writeLog ? 'cors' : 'stack')->error("CORS sync failed for {$station->slug}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$totalSynced} downloaded, {$totalSkipped} current, {$totalPruned} expired removed.");

        return self::SUCCESS;
    }

    private function parseDaysOption(): int|false|null
    {
        $days = $this->option('days');

        if ($days === null || $days === '') {
            return null;
        }

        if (! ctype_digit((string) $days) || (int) $days < 1) {
            $this->error('The --days option must be a positive integer.');

            return false;
        }

        return (int) $days;
    }
}
