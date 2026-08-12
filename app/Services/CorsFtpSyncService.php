<?php

namespace App\Services;

use App\Models\CorsFile;
use App\Models\CorsStation;
use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CorsFtpSyncService
{
    private ?Closure $logger = null;

    public function withLogger(?callable $logger): static
    {
        $this->logger = $logger ? Closure::fromCallable($logger) : null;

        return $this;
    }

    /**
     * @return array{synced: int, pruned: int, skipped: int}
     */
    public function syncStation(CorsStation $station, ?int $days = null, bool $full = false): array
    {
        $pruned = $this->pruneExpired($station);

        if ($pruned > 0) {
            $this->log("Removed {$pruned} expired file(s) for {$station->slug}.");
        }

        if ($station->is_external || ! $station->ftp_file_prefix || ! $station->ftpAccount) {
            $this->log("Skipping {$station->slug}: not syncable.");

            return ['synced' => 0, 'pruned' => $pruned, 'skipped' => 0];
        }

        if (! function_exists('ftp_connect')) {
            throw new \RuntimeException('PHP FTP extension is not enabled.');
        }

        $account = $station->ftpAccount;
        $this->log("Connecting to {$account->host}:{$account->port} for {$station->name}...");

        $connection = @ftp_connect($account->host, $account->port, 30);

        if (! $connection) {
            throw new \RuntimeException("Could not connect to FTP host {$account->host}.");
        }

        if (! @ftp_login($connection, $account->username, $account->password)) {
            ftp_close($connection);
            throw new \RuntimeException('FTP authentication failed.');
        }

        if ($account->passive) {
            ftp_pasv($connection, true);
        }

        $this->log('FTP login successful.');

        $synced = 0;
        $skipped = 0;
        $remoteRoot = rtrim($account->root_path, '/');
        $prefix = $station->ftp_file_prefix;
        $dates = $this->targetDates($days, $full);

        $this->log('Checking '.count($dates).' day folder(s) under '.$remoteRoot.' for prefix '.$prefix.'.');

        try {
            foreach ($dates as $date) {
                $result = $this->syncDay($connection, $remoteRoot, $station, $prefix, $date);
                $synced += $result['synced'];
                $skipped += $result['skipped'];
            }
        } finally {
            ftp_close($connection);
            $this->log('FTP connection closed.');
        }

        $station->update(['last_synced_at' => now()]);

        return ['synced' => $synced, 'pruned' => $pruned, 'skipped' => $skipped];
    }

    public function pruneExpired(CorsStation $station): int
    {
        $cutoff = $this->retentionCutoff();

        if (! $cutoff) {
            return 0;
        }

        $disk = Storage::disk('cors');
        $deleted = 0;

        CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where(function ($query) use ($cutoff) {
                $query->where('observed_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query->whereNull('observed_at')
                            ->where(function ($query) use ($cutoff) {
                                $query->where('year', '<', $cutoff->year)
                                    ->orWhere(function ($query) use ($cutoff) {
                                        $query->where('year', $cutoff->year)
                                            ->whereNotNull('month')
                                            ->where('month', '<', $cutoff->month);
                                    })
                                    ->orWhere(function ($query) use ($cutoff) {
                                        $query->where('year', $cutoff->year)
                                            ->where('month', $cutoff->month)
                                            ->where(function ($query) use ($cutoff) {
                                                $query->where('day', '<', $cutoff->day)
                                                    ->orWhereNull('day');
                                            });
                                    });
                            });
                    });
            })
            ->eachById(function (CorsFile $file) use ($disk, &$deleted) {
                $this->log("Deleting expired file: {$file->path}");
                $this->deleteStoredFile($disk, $file->path);
                $file->delete();
                $deleted++;
            });

        return $deleted;
    }

    /**
     * @return array{synced: int, skipped: int}
     */
    private function syncDay($connection, string $remoteRoot, CorsStation $station, string $prefix, Carbon $date): array
    {
        $synced = 0;
        $skipped = 0;

        foreach ($this->possibleDayPaths($remoteRoot, $date) as $dayPath) {
            $this->log("Checking {$dayPath}");

            $listed = $this->listDirectory($connection, $dayPath, $station);

            if ($listed === null) {
                continue;
            }

            foreach ($listed as $entry) {
                $result = $this->handleEntry($connection, $dayPath, $station, $prefix, $entry, $date);
                $synced += $result['synced'];
                $skipped += $result['skipped'];
            }

            return ['synced' => $synced, 'skipped' => $skipped];
        }

        $this->log("No day folder found for {$date->toDateString()}.");

        return ['synced' => 0, 'skipped' => 0];
    }

    /**
     * @return array{synced: int, skipped: int}
     */
    private function handleEntry($connection, string $remoteDir, CorsStation $station, string $prefix, array $entry, Carbon $date): array
    {
        if (! isset($entry['name'], $entry['type'])) {
            return ['synced' => 0, 'skipped' => 0];
        }

        $name = $entry['name'];

        if ($name === '.' || $name === '..') {
            return ['synced' => 0, 'skipped' => 0];
        }

        $remotePath = $remoteDir.'/'.$name;

        if ($entry['type'] === 'dir' && strcasecmp($name, 'data') === 0) {
            $this->log("Entering data folder: {$remotePath}");
            $listed = $this->listDirectory($connection, $remotePath, $station) ?? [];
            $synced = 0;
            $skipped = 0;

            foreach ($listed as $child) {
                if (($child['type'] ?? '') !== 'file') {
                    continue;
                }

                $result = $this->handleEntry($connection, $remotePath, $station, $prefix, $child, $date);
                $synced += $result['synced'];
                $skipped += $result['skipped'];
            }

            return ['synced' => $synced, 'skipped' => $skipped];
        }

        if ($entry['type'] !== 'file') {
            return ['synced' => 0, 'skipped' => 0];
        }

        if (! str_starts_with($name, $prefix)) {
            return ['synced' => 0, 'skipped' => 0];
        }

        $downloaded = $this->syncFile($connection, $remotePath, $station, $entry, $date);

        return $downloaded
            ? ['synced' => 1, 'skipped' => 0]
            : ['synced' => 0, 'skipped' => 1];
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function listDirectory($connection, string $remoteDir, CorsStation $station): ?array
    {
        $entries = @ftp_mlsd($connection, $remoteDir);

        if ($entries === false) {
            $this->log("Directory not found or unreadable: {$remoteDir}", 'warning');
            Log::warning('CORS FTP: failed to list directory', ['dir' => $remoteDir, 'station' => $station->slug]);

            return null;
        }

        return $entries;
    }

    private function syncFile($connection, string $remotePath, CorsStation $station, array $entry, Carbon $date): bool
    {
        $this->log("Processing file: {$remotePath}");

        $remoteMtime = isset($entry['modify']) ? strtotime($entry['modify']) : null;
        $remoteSize = isset($entry['size']) ? (int) $entry['size'] : null;
        $basename = pathinfo($remotePath, PATHINFO_FILENAME);
        $extension = pathinfo($remotePath, PATHINFO_EXTENSION);
        $relative = $this->buildLocalPath($station, $remotePath);
        $disk = Storage::disk('cors');

        $existing = $this->findExistingFile(
            $station,
            $relative,
            $basename,
            $extension,
            $date,
            $remotePath
        );

        if ($existing && $this->fileIsCurrent($existing, $disk, $remoteSize, $remoteMtime)) {
            if ($existing->remote_path !== $remotePath) {
                $existing->update(['remote_path' => $remotePath]);
            }

            $this->log("{$relative} [EXISTS AND CURRENT]");

            return false;
        }

        $disk->makeDirectory(dirname($relative));

        $tempFile = tempnam(sys_get_temp_dir(), 'cors_');

        try {
            if (! @ftp_get($connection, $tempFile, $remotePath, FTP_BINARY)) {
                $this->log("Failed to download file: {$remotePath}", 'warning');
                Log::warning('CORS FTP: download failed', ['remote' => $remotePath, 'station' => $station->slug]);

                return false;
            }

            $disk->put($relative, file_get_contents($tempFile));
            $size = $disk->size($relative);

            $this->persistFileRecord(
                $existing,
                $station,
                $relative,
                $remotePath,
                $basename,
                $extension,
                $size,
                $date,
                $remoteMtime
            );

            $this->log($existing ? "{$relative} [UPDATED]" : "{$relative} [DOWNLOADED]");

            return true;
        } catch (Throwable $e) {
            $this->log("Sync error for {$remotePath}: {$e->getMessage()}", 'error');
            Log::error('CORS FTP: sync error', [
                'remote' => $remotePath,
                'station' => $station->slug,
                'error' => $e->getMessage(),
            ]);

            return false;
        } finally {
            if (is_file($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    private function findExistingFile(
        CorsStation $station,
        string $relativePath,
        string $basename,
        string $extension,
        Carbon $date,
        string $remotePath
    ): ?CorsFile {
        $byPath = CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where('path', $relativePath)
            ->first();

        if ($byPath) {
            return $byPath;
        }

        $byRemote = CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where('remote_path', $remotePath)
            ->first();

        if ($byRemote) {
            return $byRemote;
        }

        $query = CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where('name', $basename)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->where('day', $date->day);

        if ($extension !== '') {
            $query->where('ext', $extension);
        } else {
            $query->where(function ($query) {
                $query->whereNull('ext')->orWhere('ext', '');
            });
        }

        return $query->first();
    }

    private function fileIsCurrent(
        CorsFile $existing,
        Filesystem $disk,
        ?int $remoteSize,
        ?int $remoteMtime
    ): bool {
        if (! $disk->exists($existing->path)) {
            return false;
        }

        $localSize = $disk->size($existing->path);
        $localMtime = $disk->lastModified($existing->path);

        if ($remoteSize !== null && $localSize !== $remoteSize) {
            return false;
        }

        if ($remoteMtime !== null && $localMtime < $remoteMtime) {
            return false;
        }

        return $existing->size === $localSize;
    }

    private function persistFileRecord(
        ?CorsFile $existing,
        CorsStation $station,
        string $relative,
        string $remotePath,
        string $basename,
        string $extension,
        int $size,
        Carbon $date,
        ?int $remoteMtime
    ): void {
        $disk = Storage::disk('cors');
        $attributes = [
            'name' => $basename,
            'ext' => $extension !== '' ? $extension : null,
            'size' => $size,
            'path' => $relative,
            'remote_path' => $remotePath,
            'year' => $date->year,
            'month' => $date->month,
            'day' => $date->day,
            'observed_at' => $remoteMtime
                ? Carbon::createFromTimestamp($remoteMtime)
                : $date->copy()->startOfDay(),
        ];

        if ($existing && $existing->path !== $relative) {
            $oldPath = $existing->path;
            $existing->delete();

            if ($disk->exists($oldPath)) {
                $this->deleteStoredFile($disk, $oldPath);
            }
        }

        CorsFile::updateOrCreate(
            [
                'cors_station_id' => $station->id,
                'path' => $relative,
            ],
            $attributes
        );
    }

    /**
     * @return list<Carbon>
     */
    private function targetDates(?int $days, bool $full): array
    {
        if ($full) {
            $start = ($this->retentionCutoff() ?? now()->subMonthsNoOverflow(3))->startOfDay();
            $dates = [];

            for ($date = $start->copy(); $date->lte(now()->startOfDay()); $date->addDay()) {
                $dates[] = $date->copy();
            }

            return $dates;
        }

        $window = $days && $days > 0
            ? $days
            : max(1, (int) config('services.cors_ftp.sync_days', 2));

        $dates = [];

        for ($i = 0; $i < $window; $i++) {
            $dates[] = now()->subDays($i)->startOfDay();
        }

        return $dates;
    }

    /**
     * @return list<string>
     */
    private function possibleDayPaths(string $remoteRoot, Carbon $date): array
    {
        $padded = $remoteRoot.'/'.$date->format('Y/m/d');
        $unpadded = $remoteRoot.'/'.$date->format('Y/n/j');

        return $padded === $unpadded ? [$padded] : [$padded, $unpadded];
    }

    private function retentionCutoff(): ?Carbon
    {
        $months = (int) config('services.cors_ftp.retention_months', 3);

        if ($months <= 0) {
            return null;
        }

        return now()->subMonthsNoOverflow($months)->startOfDay();
    }

    private function deleteStoredFile(Filesystem $disk, ?string $path): void
    {
        if (! $path) {
            return;
        }

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        $this->removeEmptyDirectories($disk, dirname($path));
    }

    private function removeEmptyDirectories(Filesystem $disk, string $directory): void
    {
        while ($directory && $directory !== '.' && $directory !== DIRECTORY_SEPARATOR) {
            if ($disk->files($directory) || $disk->directories($directory)) {
                break;
            }

            $disk->deleteDirectory($directory);
            $directory = dirname($directory);
        }
    }

    private function buildLocalPath(CorsStation $station, string $remotePath): string
    {
        $folder = trim((string) $station->ftp_local_directory);
        $folder = str_replace(['\\', '/', '..'], '', $folder);

        if ($folder === '') {
            $folder = strtoupper(str_replace(['\\', '/', '..'], '', $station->slug));
        }

        $relative = ltrim(str_replace(['\\', '..'], ['/', ''], $remotePath), '/');
        $remoteRoot = trim((string) ($station->ftpAccount?->root_path ?: config('services.cors_ftp.root')), '/');

        if ($remoteRoot !== '') {
            $relative = preg_replace('#^'.preg_quote($remoteRoot, '#').'/?#i', '', $relative) ?? $relative;
        }

        $relative = preg_replace('#^RINEX[^/]*/#i', '', $relative) ?? $relative;
        $relative = ltrim($relative, '/');

        return $relative === '' ? $folder : $folder.'/'.$relative;
    }

    private function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            ($this->logger)($level, $message);
        }
    }
}
