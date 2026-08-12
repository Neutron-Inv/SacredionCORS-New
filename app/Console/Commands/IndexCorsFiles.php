<?php

namespace App\Console\Commands;

use App\Models\CorsStation;
use App\Services\CorsLocalIndexService;
use Illuminate\Console\Command;

class IndexCorsFiles extends Command
{
    protected $signature = 'cors:index
        {station? : Station slug to index (omit for all)}
        {--prune : Remove database rows whose files no longer exist on disk}
        {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Scan local CORS storage and register existing files in the database';

    public function handle(CorsLocalIndexService $indexService): int
    {
        $slug = $this->argument('station');
        $prune = (bool) $this->option('prune');
        $dryRun = (bool) $this->option('dry-run');

        $stations = CorsStation::query()
            ->where('is_active', true)
            ->where('is_external', false)
            ->when($slug, fn ($query) => $query->where('slug', $slug))
            ->orderBy('sort_order')
            ->get();

        if ($stations->isEmpty()) {
            $this->error($slug ? "No station found for slug [{$slug}]." : 'No indexable stations found.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->comment('Dry run — no database changes will be made.');
        }

        $totalIndexed = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalPruned = 0;

        foreach ($stations as $station) {
            $root = trim((string) $station->ftp_local_directory) ?: strtoupper($station->slug);
            $this->info("Indexing {$station->name} ({$station->slug}) under {$root}/...");

            $result = $indexService->indexStation($station, $prune, $dryRun);

            $totalIndexed += $result['indexed'];
            $totalUpdated += $result['updated'];
            $totalSkipped += $result['skipped'];
            $totalPruned += $result['pruned'];

            $this->line("  → {$result['indexed']} new, {$result['updated']} updated, {$result['skipped']} skipped".($prune ? ", {$result['pruned']} pruned" : '').'.');
        }

        $summary = "Done. {$totalIndexed} new, {$totalUpdated} updated, {$totalSkipped} skipped";

        if ($prune) {
            $summary .= ", {$totalPruned} pruned";
        }

        $this->info($summary.($dryRun ? ' (dry run)' : '').'.');

        return self::SUCCESS;
    }
}
