<?php

namespace App\Services;

use App\Models\CorsFile;
use App\Models\CorsStation;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CorsLocalIndexService
{
    /**
     * @return array{indexed: int, updated: int, skipped: int, pruned: int}
     */
    public function indexStation(CorsStation $station, bool $prune = false, bool $dryRun = false): array
    {
        if ($station->is_external) {
            return ['indexed' => 0, 'updated' => 0, 'skipped' => 0, 'pruned' => 0];
        }

        $disk = Storage::disk('cors');
        $root = $this->stationRoot($station);

        if ($root === '' || ! $disk->exists($root)) {
            return ['indexed' => 0, 'updated' => 0, 'skipped' => 0, 'pruned' => 0];
        }

        $prefix = $station->ftp_file_prefix;
        $indexed = 0;
        $updated = 0;
        $skipped = 0;
        $seenPaths = [];

        foreach ($disk->allFiles($root) as $relativePath) {
            if (! $this->isIndexableFile($relativePath, $prefix)) {
                $skipped++;

                continue;
            }

            $seenPaths[] = $relativePath;
            $result = $this->indexFile($disk, $station, $relativePath, $dryRun);

            if ($result === 'created') {
                $indexed++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }
        }

        $pruned = $prune
            ? $this->pruneMissing($station, $disk, $seenPaths, $dryRun)
            : 0;

        if (! $dryRun) {
            $station->update(['last_synced_at' => now()]);
        }

        return [
            'indexed' => $indexed,
            'updated' => $updated,
            'skipped' => $skipped,
            'pruned' => $pruned,
        ];
    }

    private function indexFile(Filesystem $disk, CorsStation $station, string $relativePath, bool $dryRun): string
    {
        $filename = basename($relativePath);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: null;
        $size = $disk->size($relativePath);
        $mtime = $disk->lastModified($relativePath);
        $observedAt = Carbon::createFromTimestamp($mtime);
        $dateParts = $this->parseDateFromPath($relativePath) ?? [
            'year' => $observedAt->year,
            'month' => $observedAt->month,
            'day' => $observedAt->day,
        ];

        $existing = CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where('path', $relativePath)
            ->first();

        $attributes = [
            'name' => $basename,
            'ext' => $extension,
            'size' => $size,
            'year' => $dateParts['year'],
            'month' => $dateParts['month'],
            'day' => $dateParts['day'],
            'observed_at' => $observedAt,
        ];

        if ($existing) {
            $changed = $existing->size !== $size
                || $existing->observed_at?->getTimestamp() !== $observedAt->getTimestamp()
                || $existing->name !== $basename
                || $existing->ext !== $extension;

            if (! $changed) {
                return 'skipped';
            }

            if (! $dryRun) {
                $existing->update($attributes);
            }

            return 'updated';
        }

        if ($dryRun) {
            return 'created';
        }

        CorsFile::create(array_merge($attributes, [
            'cors_station_id' => $station->id,
            'path' => $relativePath,
            'remote_path' => null,
        ]));

        return 'created';
    }

    /**
     * @param  list<string>  $existingPaths
     */
    private function pruneMissing(CorsStation $station, Filesystem $disk, array $existingPaths, bool $dryRun): int
    {
        $existingPaths = array_flip($existingPaths);
        $pruned = 0;

        CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->eachById(function (CorsFile $file) use ($disk, $existingPaths, $dryRun, &$pruned) {
                if (isset($existingPaths[$file->path]) || $disk->exists($file->path)) {
                    return;
                }

                if (! $dryRun) {
                    $file->delete();
                }

                $pruned++;
            });

        return $pruned;
    }

    private function stationRoot(CorsStation $station): string
    {
        $folder = trim((string) $station->ftp_local_directory);
        $folder = str_replace(['\\', '/', '..'], '', $folder);

        if ($folder === '') {
            $folder = strtoupper(str_replace(['\\', '/', '..'], '', $station->slug));
        }

        return $folder;
    }

    private function isIndexableFile(string $relativePath, ?string $prefix): bool
    {
        $filename = basename($relativePath);

        if ($filename === '' || str_starts_with($filename, '.')) {
            return false;
        }

        if ($prefix !== null && $prefix !== '' && ! str_starts_with($filename, $prefix)) {
            return false;
        }

        return true;
    }

    /**
     * @return array{year: int, month: int, day: int}|null
     */
    private function parseDateFromPath(string $path): ?array
    {
        if (! preg_match('#/(\\d{4})/(\\d{1,2})/(\\d{1,2})(?:/|$)#', '/'.$path, $matches)) {
            return null;
        }

        return [
            'year' => (int) $matches[1],
            'month' => (int) $matches[2],
            'day' => (int) $matches[3],
        ];
    }
}
