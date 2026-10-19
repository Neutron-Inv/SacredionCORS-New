<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorsDailyOrder extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const PROCESSING = 'processing';

    public const READY = 'ready';

    public const FAILED = 'failed';

    public const EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'cors_station_id',
        'observed_on',
        'file_count',
        'amount',
        'currency',
        'status',
        'payment_reference',
        'payment_access_code',
        'payment_channel',
        'paid_at',
        'archive_path',
        'archive_name',
        'archive_size',
        'download_token',
        'ready_at',
        'ready_notified_at',
        'expires_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'file_count' => 'integer',
            'amount' => 'integer',
            'archive_size' => 'integer',
            'paid_at' => 'datetime',
            'ready_at' => 'datetime',
            'ready_notified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(CorsStation::class, 'cors_station_id');
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('cors.daily.download', $this->download_token);
    }

    public function getFormattedAmountAttribute(): string
    {
        $major = $this->amount / 100;
        $formatted = fmod($major, 1.0) === 0.0
            ? number_format($major)
            : number_format($major, 2);

        return $this->currency === 'NGN'
            ? '₦'.$formatted
            : $this->currency.' '.$formatted;
    }

    public function isDownloadable(): bool
    {
        return $this->status === self::READY
            && $this->archive_path
            && $this->expires_at?->isFuture();
    }
}
