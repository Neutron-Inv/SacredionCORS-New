<?php

namespace App\Services;

use App\Models\CorsFile;
use App\Models\CorsStation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CorsFtpSyncService
{
    public function syncStation(CorsStation $station): int
    {
        if ($station->is_external || ! $station->ftp_file_prefix || ! $station->ftpAccount) {
            return 0;
        }

        if (! function_exists('ftp_connect')) {
            throw new \RuntimeException('PHP FTP extension is not enabled.');
        }

        $account = $station->ftpAccount;
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

        $synced = 0;
        $remoteRoot = rtrim($account->root_path, '/');
        $prefix = $station->ftp_file_prefix;

        try {
            $synced = $this->syncDirectory(
                $connection,
                $remoteRoot,
                $station,
                $prefix,
                $remoteRoot
            );
        } finally {
            ftp_close($connection);
        }

        $station->update(['last_synced_at' => now()]);

        return $synced;
    }

    private function syncDirectory(
        $connection,
        string $remoteDir,
        CorsStation $station,
        string $prefix,
        string $remoteRoot
    ): int {
        $entries = @ftp_mlsd($connection, $remoteDir);

        if ($entries === false) {
            Log::warning('CORS FTP: failed to list directory', ['dir' => $remoteDir, 'station' => $station->slug]);

            return 0;
        }

        $synced = 0;

        foreach ($entries as $entry) {
            if (! isset($entry['name'], $entry['type'])) {
                continue;
            }

            $name = $entry['name'];

            if ($name === '.' || $name === '..') {
                continue;
            }

            $remotePath = $remoteDir.'/'.$name;

            if ($entry['type'] === 'dir') {
                $synced += $this->syncDirectory($connection, $remotePath, $station, $prefix, $remoteRoot);
                continue;
            }

            if ($entry['type'] !== 'file' || ! str_starts_with($name, $prefix)) {
                continue;
            }

            if ($this->syncFile($connection, $remotePath, $station, $entry)) {
                $synced++;
            }
        }

        return $synced;
    }

    private function syncFile($connection, string $remotePath, CorsStation $station, array $entry): bool
    {
        $basename = pathinfo($remotePath, PATHINFO_FILENAME);
        $extension = pathinfo($remotePath, PATHINFO_EXTENSION);
        $relative = $this->buildLocalPath($station, $remotePath);
        $disk = Storage::disk('cors');

        $remoteMtime = isset($entry['modify']) ? strtotime($entry['modify']) : null;
        $existing = CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->where('remote_path', $remotePath)
            ->first();

        if ($existing && $disk->exists($existing->path)) {
            $localMtime = $disk->lastModified($existing->path);

            if ($remoteMtime && $localMtime >= $remoteMtime && $existing->size === ($entry['size'] ?? $existing->size)) {
                return false;
            }
        }

        $disk->makeDirectory(dirname($relative));

        $tempFile = tempnam(sys_get_temp_dir(), 'cors_');

        try {
            if (! @ftp_get($connection, $tempFile, $remotePath, FTP_BINARY)) {
                Log::warning('CORS FTP: download failed', ['remote' => $remotePath, 'station' => $station->slug]);

                return false;
            }

            $disk->put($relative, file_get_contents($tempFile));
            $size = $disk->size($relative);
            [$year, $month, $day] = $this->parseDateFromPath($remotePath, $remoteMtime);

            CorsFile::updateOrCreate(
                [
                    'cors_station_id' => $station->id,
                    'remote_path' => $remotePath,
                ],
                [
                    'name' => $basename,
                    'ext' => $extension,
                    'size' => $size,
                    'path' => $relative,
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'observed_at' => $this->buildObservedAt($year, $month, $day, $remoteMtime),
                ]
            );

            return true;
        } catch (Throwable $e) {
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

    private function buildLocalPath(CorsStation $station, string $remotePath): string
    {
        $sanitized = ltrim(str_replace(['\\', '..'], ['/', ''], $remotePath), '/');

        return $station->slug.'/'.$sanitized;
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?int}
     */
    private function parseDateFromPath(string $remotePath, ?int $fallbackTimestamp): array
    {
        $segments = array_values(array_filter(explode('/', $remotePath)));

        $year = null;
        $month = null;
        $day = null;

        foreach ($segments as $index => $segment) {
            if (preg_match('/^\d{4}$/', $segment)) {
                $year = (int) $segment;
                $month = isset($segments[$index + 1]) && is_numeric($segments[$index + 1]) ? (int) $segments[$index + 1] : null;
                $day = isset($segments[$index + 2]) && is_numeric($segments[$index + 2]) ? (int) $segments[$index + 2] : null;
                break;
            }
        }

        if (! $year && $fallbackTimestamp) {
            $year = (int) date('Y', $fallbackTimestamp);
            $month = (int) date('m', $fallbackTimestamp);
            $day = (int) date('d', $fallbackTimestamp);
        }

        return [$year, $month, $day];
    }

    private function buildObservedAt(?int $year, ?int $month, ?int $day, ?int $fallbackTimestamp): ?\DateTimeInterface
    {
        if ($year && $month && $day) {
            return now()->setDate($year, $month, $day)->startOfDay();
        }

        return $fallbackTimestamp ? now()->setTimestamp($fallbackTimestamp) : null;
    }
}
