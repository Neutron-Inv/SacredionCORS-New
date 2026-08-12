<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorsFile extends Model
{
    protected $fillable = [
        'cors_station_id',
        'name',
        'ext',
        'size',
        'path',
        'remote_path',
        'year',
        'month',
        'day',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'year' => 'integer',
            'month' => 'integer',
            'day' => 'integer',
            'observed_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(CorsStation::class, 'cors_station_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function getDownloadNameAttribute(): string
    {
        return $this->ext ? "{$this->name}.{$this->ext}" : $this->name;
    }

    public function getFormattedObservedAtAttribute(): ?string
    {
        if (! $this->observed_at) {
            return null;
        }

        return $this->observed_at->format('M j, Y g:i A');
    }
}
