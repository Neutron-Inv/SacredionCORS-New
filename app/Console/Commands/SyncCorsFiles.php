<?php

namespace App\Console\Commands;

use App\Models\CorsStation;
use App\Services\CorsFtpSyncService;
use Illuminate\Console\Command;

class SyncCorsFiles extends Command
{
    protected $signature = 'cors:sync {station? : Station slug to sync (omit for all)}';

    protected $description = 'Sync GNSS RINEX files from FTP into local storage and the database';

    public function handle(CorsFtpSyncService $syncService): int
    {
        $slug = $this->argument('station');

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

        $total = 0;

        foreach ($stations as $station) {
            $this->info("Syncing {$station->name} ({$station->slug})...");

            try {
                $count = $syncService->syncStation($station);
                $total += $count;
                $this->line("  → {$count} file(s) updated.");
            } catch (\Throwable $e) {
                $this->error("  → Failed: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$total} file(s) synced.");

        return self::SUCCESS;
    }
}
