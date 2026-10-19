<?php

namespace App\Services;

use App\Models\CorsFile;
use App\Models\CorsStation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CorsDailyCatalog
{
    /**
     * @return Collection<int, Carbon>
     */
    public function availableDates(CorsStation $station): Collection
    {
        $today = $this->today();

        return CorsFile::query()
            ->where('cors_station_id', $station->id)
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->whereNotNull('day')
            ->select('year', 'month', 'day')
            ->distinct()
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('day')
            ->get()
            ->filter(fn (CorsFile $row) => checkdate((int) $row->month, (int) $row->day, (int) $row->year))
            ->map(fn (CorsFile $row) => $this->date((int) $row->year, (int) $row->month, (int) $row->day))
            ->filter(fn (Carbon $date) => $date->lt($today))
            ->values();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function dateOptions(CorsStation $station): array
    {
        return $this->availableDates($station)
            ->map(fn (Carbon $date) => [
                'value' => $date->toDateString(),
                'label' => $date->format('D, M j, Y'),
            ])
            ->all();
    }

    /**
     * @return array{date: string, count: int, extensions: array<string, int>}
     */
    public function summary(CorsStation $station, string $date): array
    {
        $observedOn = $this->parseOrderableDate($date);
        $files = $this->filesFor($station, $observedOn);

        if ($files->isEmpty()) {
            throw ValidationException::withMessages([
                'date' => 'No files were found for that day.',
            ]);
        }

        return [
            'date' => $observedOn->toDateString(),
            'count' => $files->count(),
            'extensions' => $files
                ->groupBy(fn (CorsFile $file) => strtolower($file->ext ?: 'file'))
                ->map(fn (Collection $group) => $group->count())
                ->sortKeys()
                ->all(),
        ];
    }

    public function parseOrderableDate(string $date): Carbon
    {
        $timezone = $this->timezone();
        $parsed = Carbon::createFromFormat('Y-m-d', $date, $timezone);

        if (! $parsed || $parsed->format('Y-m-d') !== $date) {
            throw ValidationException::withMessages([
                'date' => 'Choose a valid date.',
            ]);
        }

        $observedOn = $parsed->startOfDay();

        if ($observedOn->greaterThanOrEqualTo($this->today())) {
            throw ValidationException::withMessages([
                'date' => 'Choose a completed day. Today is still being recorded.',
            ]);
        }

        return $observedOn;
    }

    /**
     * @return Collection<int, CorsFile>
     */
    public function filesFor(CorsStation $station, Carbon $date): Collection
    {
        return $station->files()
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->where('day', $date->day)
            ->orderBy('name')
            ->orderBy('ext')
            ->get();
    }

    public function today(): Carbon
    {
        return now($this->timezone())->startOfDay();
    }

    public function timezone(): string
    {
        return (string) config('cors.daily.timezone', 'Africa/Lagos');
    }

    private function date(int $year, int $month, int $day): Carbon
    {
        return Carbon::create($year, $month, $day, 0, 0, 0, $this->timezone())->startOfDay();
    }
}
