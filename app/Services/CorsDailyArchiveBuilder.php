<?php

namespace App\Services;

use App\Models\CorsDailyOrder;
use App\Models\CorsFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class CorsDailyArchiveBuilder
{
    public function __construct(
        private CorsDailyCatalog $catalog,
        private RinexMerger $merger,
    ) {}

    /**
     * @return array{path: string, size: int, name: string}
     */
    public function build(CorsDailyOrder $order): array
    {
        $order->loadMissing('station');
        $station = $order->station;

        if (! $station) {
            throw new RuntimeException('The station for this order no longer exists.');
        }

        $files = $this->catalog->filesFor($station, $order->observed_on);
        $groups = $this->groupsOnDisk($files);

        if ($groups === []) {
            throw new RuntimeException('No observation files were found on disk for that day.');
        }

        $disk = Storage::disk('local');
        $tempRelative = 'cors-daily-tmp/'.$order->id;
        $zipRelative = 'cors-daily/'.$order->id.'.zip';

        $disk->delete($zipRelative);
        $disk->deleteDirectory($tempRelative);
        $disk->makeDirectory($tempRelative);
        $disk->makeDirectory('cors-daily');

        $tempAbsolute = $disk->path($tempRelative);
        $written = [];

        try {
            foreach ($groups as $extension => $paths) {
                $outputName = $this->uniqueName($this->merger->dailyName($paths[0]), $written);
                $target = $tempAbsolute.DIRECTORY_SEPARATOR.$outputName;
                $this->merger->merge(
                    $paths,
                    $target,
                    $this->merger->resolvesInterval(
                        (string) $extension,
                        (int) config('cors.daily.interval'),
                        config('cors.daily.shorten_extensions', []),
                    ),
                );
                $written[$outputName] = $target;
            }

            $zipAbsolute = $disk->path($zipRelative);
            $zip = new ZipArchive;

            if ($zip->open($zipAbsolute, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not create the download archive.');
            }

            foreach ($written as $name => $path) {
                $zip->addFile($path, $name);
            }

            $zip->close();
        } catch (\Throwable $exception) {
            $disk->delete($zipRelative);
            $disk->deleteDirectory($tempRelative);

            throw $exception;
        }

        $disk->deleteDirectory($tempRelative);

        return [
            'path' => $zipRelative,
            'size' => $disk->size($zipRelative),
            'name' => $station->slug.'-24h-'.$order->observed_on->toDateString().'.zip',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CorsFile>  $files
     * @return array<string, list<string>>
     */
    private function groupsOnDisk($files): array
    {
        $groups = [];

        foreach ($files as $file) {
            $absolute = Storage::disk('cors')->path($file->path);

            if (! is_file($absolute)) {
                continue;
            }

            $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION) ?: 'file');
            $groups[$extension][] = $absolute;
        }

        ksort($groups);

        foreach ($groups as $extension => $paths) {
            usort($paths, fn (string $left, string $right) => strnatcasecmp(basename($left), basename($right)));
            $groups[$extension] = $paths;
        }

        return $groups;
    }

    /**
     * @param  array<string, string>  $written
     */
    private function uniqueName(string $name, array $written): string
    {
        if (! isset($written[$name])) {
            return $name;
        }

        $filename = pathinfo($name, PATHINFO_FILENAME);
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        return $filename.'-'.(count($written) + 1).($extension !== '' ? '.'.$extension : '');
    }
}
