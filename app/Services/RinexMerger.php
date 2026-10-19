<?php

namespace App\Services;

use RuntimeException;

/**
 * Merges one RINEX type the way MergeRinexFiles.exe does: files listed together
 * (same extension, in order) become a single file with one header and every
 * observation or navigation record after it. Observation files whose extension
 * matches the configured suffix are thinned to the configured interval.
 */
class RinexMerger
{
    private ?string $pushback = null;

    /**
     * @var array<string, array{offset: int, bytes: string, system: string}>
     */
    private array $headerSlots = [];

    /**
     * @var array{year: int, month: int, day: int, hour: int, minute: int, second: float}|null
     */
    private ?array $firstKept = null;

    /**
     * @var array{year: int, month: int, day: int, hour: int, minute: int, second: float}|null
     */
    private ?array $lastKept = null;

    /**
     * @param  list<string>  $paths
     * @param  list<string>  $suffixes
     */
    public function resolvesInterval(string $extension, int $interval, array $suffixes): ?int
    {
        if ($interval < 2) {
            return null;
        }

        $extension = strtolower($extension);

        foreach ($suffixes as $suffix) {
            $suffix = strtolower(ltrim(trim((string) $suffix), '.'));

            if ($suffix !== '' && str_ends_with($extension, $suffix)) {
                return $interval;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $paths
     */
    public function merge(array $paths, string $outputPath, ?int $intervalSeconds = null): void
    {
        if ($paths === []) {
            throw new RuntimeException('No files to merge.');
        }

        $intervalSeconds = ($intervalSeconds !== null && $intervalSeconds >= 2) ? $intervalSeconds : null;
        $this->headerSlots = [];
        $this->firstKept = null;
        $this->lastKept = null;

        $lastObsLine = $this->headerLine($paths[array_key_last($paths)], 'TIME OF LAST OBS');
        $output = fopen($outputPath, 'wb');

        if ($output === false) {
            throw new RuntimeException('Could not create the merged RINEX file.');
        }

        try {
            foreach (array_values($paths) as $index => $path) {
                $this->appendFile($output, $path, $index === 0, $lastObsLine, $intervalSeconds);
            }

            if ($intervalSeconds !== null) {
                $this->patchObservationTimes($output);
            }
        } finally {
            fclose($output);
        }
    }

    /**
     * RINEX 2 daily files use session character 0 ("file contains all the data of the day").
     * KOGI289A.26o becomes KOGI2890.26o.
     */
    public function dailyName(string $path): string
    {
        $base = basename($path);

        if (preg_match('/^([A-Za-z0-9]{4}\d{3})[0-9A-Xa-x](\..+)$/', $base, $matches)) {
            return $matches[1].'0'.$matches[2];
        }

        return $base;
    }

    private function appendFile($output, string $path, bool $isFirst, ?string $lastObsLine, ?int $interval): void
    {
        $input = fopen($path, 'rb');

        if ($input === false) {
            throw new RuntimeException('Could not read '.basename($path).'.');
        }

        $this->pushback = null;
        $sawEnd = false;
        $newline = "\n";

        try {
            while (($line = $this->readLine($input)) !== false) {
                if (str_ends_with($line, "\r\n")) {
                    $newline = "\r\n";
                }

                $isEnd = ! $sawEnd && str_contains($line, 'END OF HEADER');

                if (! $sawEnd) {
                    if ($isEnd) {
                        $sawEnd = true;

                        if ($isFirst) {
                            $this->writeHeaderLine($output, $this->commentLine($newline), '');
                            $this->writeHeaderLine($output, $line, '');
                        }

                        continue;
                    }

                    if ($isFirst) {
                        if ($lastObsLine && str_contains($line, 'TIME OF LAST OBS')) {
                            $line = $lastObsLine;
                        }

                        if ($interval !== null && str_contains($line, 'INTERVAL')) {
                            $line = $this->overwrite($line, $this->intervalContent($interval));
                        }

                        $label = $this->headerLabel($line);
                        $this->writeHeaderLine($output, $line, $label ?? '');
                    }

                    continue;
                }

                $epoch = $interval !== null ? $this->parseEpoch($line) : null;

                if ($epoch === null) {
                    fwrite($output, $line);

                    continue;
                }

                $keep = $epoch['flag'] >= 2 || $this->onInterval($epoch['secondsOfDay'], $interval);
                if ($keep) {
                    fwrite($output, $line);
                    $this->rememberEpoch($epoch);
                }

                $this->copyEpochBody($input, $output, $epoch['satellites'], $keep);
            }

            if (! $sawEnd && ! $isFirst) {
                rewind($input);
                stream_copy_to_stream($input, $output);
            }
        } finally {
            fclose($input);
        }
    }

    private function copyEpochBody($input, $output, int $satellites, bool $keep): void
    {
        $seen = 0;

        while ($seen < $satellites) {
            $line = $this->readLine($input);

            if ($line === false) {
                return;
            }

            if ($this->parseEpoch($line) !== null) {
                $this->pushback = $line;

                return;
            }

            if ($keep) {
                fwrite($output, $line);
            }

            if (preg_match('/^[A-Z]\d{2}/', $line) === 1) {
                $seen++;
            }
        }
    }

    /**
     * @param  array{year: int, month: int, day: int, hour: int, minute: int, second: float}  $epoch
     */
    private function rememberEpoch(array $epoch): void
    {
        $stamp = [
            'year' => $epoch['year'],
            'month' => $epoch['month'],
            'day' => $epoch['day'],
            'hour' => $epoch['hour'],
            'minute' => $epoch['minute'],
            'second' => $epoch['second'],
        ];

        $this->firstKept ??= $stamp;
        $this->lastKept = $stamp;
    }

    private function patchObservationTimes($output): void
    {
        if ($this->firstKept === null || $this->lastKept === null) {
            return;
        }

        foreach ([
            'TIME OF FIRST OBS' => $this->firstKept,
            'TIME OF LAST OBS' => $this->lastKept,
        ] as $label => $epoch) {
            $slot = $this->headerSlots[$label] ?? null;

            if ($slot === null) {
                continue;
            }

            fseek($output, $slot['offset']);
            fwrite($output, $this->overwrite($slot['bytes'], $this->observationTimeContent($epoch, $label, $slot['system'])));
        }
    }

    private function writeHeaderLine($output, string $line, string $label): void
    {
        $offset = ftell($output);
        fwrite($output, $line);

        if (in_array($label, ['TIME OF FIRST OBS', 'TIME OF LAST OBS'], true)) {
            $this->headerSlots[$label] = [
                'offset' => $offset,
                'bytes' => $line,
                'system' => $this->timeSystem($line),
            ];
        }
    }

    private function headerLabel(string $line): ?string
    {
        if (preg_match('/TIME OF (?:FIRST|LAST) OBS/', $line, $matches) === 1) {
            return trim($matches[0]);
        }

        return null;
    }

    private function timeSystem(string $line): string
    {
        if (preg_match('/([A-Z]{3})\s+TIME OF (?:FIRST|LAST) OBS/', $line, $matches) === 1) {
            return $matches[1];
        }

        return 'GPS';
    }

    private function intervalContent(int $interval): string
    {
        return str_pad(sprintf('%10.3f', $interval), 60).str_pad('INTERVAL', 20);
    }

    /**
     * @param  array{year: int, month: int, day: int, hour: int, minute: int, second: float}  $epoch
     */
    private function observationTimeContent(array $epoch, string $label, string $system): string
    {
        $value = sprintf(
            '  %4d    %2d    %2d    %2d    %2d%13.7f     %-3s',
            $epoch['year'],
            $epoch['month'],
            $epoch['day'],
            $epoch['hour'],
            $epoch['minute'],
            $epoch['second'],
            $system,
        );

        return str_pad(substr($value, 0, 60), 60).str_pad($label, 20);
    }

    private function overwrite(string $original, string $content): string
    {
        $newline = '';

        if (str_ends_with($original, "\r\n")) {
            $newline = "\r\n";
            $original = substr($original, 0, -2);
        } elseif (str_ends_with($original, "\n")) {
            $newline = "\n";
            $original = substr($original, 0, -1);
        }

        return substr(str_pad($content, strlen($original)), 0, strlen($original)).$newline;
    }

    /**
     * @return array{year: int, month: int, day: int, hour: int, minute: int, second: float, flag: int, satellites: int, secondsOfDay: float}|null
     */
    private function parseEpoch(string $line): ?array
    {
        if (preg_match(
            '/^>\s*(\d{4})\s+(\d{1,2})\s+(\d{1,2})\s+(\d{1,2})\s+(\d{1,2})\s+([0-9]+(?:\.[0-9]*)?)\s+(\d+)\s+(\d+)/',
            rtrim($line, "\r\n"),
            $matches,
        ) !== 1) {
            return null;
        }

        $second = (float) $matches[6];

        return [
            'year' => (int) $matches[1],
            'month' => (int) $matches[2],
            'day' => (int) $matches[3],
            'hour' => (int) $matches[4],
            'minute' => (int) $matches[5],
            'second' => $second,
            'flag' => (int) $matches[7],
            'satellites' => (int) $matches[8],
            'secondsOfDay' => ((int) $matches[4]) * 3600 + ((int) $matches[5]) * 60 + $second,
        ];
    }

    private function onInterval(float $secondsOfDay, int $interval): bool
    {
        $remainder = fmod($secondsOfDay, $interval);

        if ($remainder < 0) {
            $remainder += $interval;
        }

        return $remainder <= 0.001 || abs($remainder - $interval) <= 0.001;
    }

    private function readLine($input): string|false
    {
        if ($this->pushback !== null) {
            $line = $this->pushback;
            $this->pushback = null;

            return $line;
        }

        $line = fgets($input);

        return $line === false ? false : $line;
    }

    private function headerLine(string $path, string $label): ?string
    {
        $input = fopen($path, 'rb');

        if ($input === false) {
            return null;
        }

        try {
            while (($line = fgets($input)) !== false) {
                if (str_contains($line, $label)) {
                    return $line;
                }

                if (str_contains($line, 'END OF HEADER')) {
                    return null;
                }
            }
        } finally {
            fclose($input);
        }

        return null;
    }

    private function commentLine(string $newline): string
    {
        return str_pad('Sacredion 24-hour merge', 60).str_pad('COMMENT', 20).$newline;
    }
}
