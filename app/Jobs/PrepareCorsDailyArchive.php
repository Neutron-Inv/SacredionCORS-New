<?php

namespace App\Jobs;

use App\Mail\CorsDailyOrderFailed;
use App\Mail\CorsDailyOrderReady;
use App\Models\CorsDailyOrder;
use App\Services\CorsDailyArchiveBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PrepareCorsDailyArchive implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 1800;

    public int $uniqueFor = 3600;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cors-daily-'.$this->orderId))
                ->expireAfter(1800)
                ->dontRelease(),
        ];
    }

    public function handle(CorsDailyArchiveBuilder $builder): void
    {
        $order = CorsDailyOrder::with(['station', 'user'])->find($this->orderId);

        if (! $order?->paid_at || $order->status === CorsDailyOrder::EXPIRED) {
            return;
        }

        if ($order->status === CorsDailyOrder::READY && $order->ready_notified_at) {
            return;
        }

        $order->update([
            'status' => CorsDailyOrder::PROCESSING,
            'failure_reason' => null,
        ]);

        Log::info('Preparing 24-hour CORS archive', [
            'order_id' => $order->id,
            'station' => $order->station?->slug,
            'date' => $order->observed_on?->toDateString(),
            'files' => $order->file_count,
        ]);

        if (! $order->archive_path || ! Storage::disk('local')->exists($order->archive_path)) {
            $archive = $builder->build($order);
            $order->update([
                'archive_path' => $archive['path'],
                'archive_size' => $archive['size'],
                'archive_name' => $archive['name'],
            ]);
        }

        $days = max(1, (int) config('cors.daily.download_days', 7));

        $order->forceFill([
            'status' => CorsDailyOrder::READY,
            'ready_at' => $order->ready_at ?? now(),
            'expires_at' => $order->expires_at ?? now()->addDays($days),
        ])->save();

        Mail::to($order->user)->send(new CorsDailyOrderReady($order));

        $order->update(['ready_notified_at' => now()]);
    }

    public function failed(?Throwable $exception): void
    {
        $order = CorsDailyOrder::with('user')->find($this->orderId);

        if (! $order || $order->status === CorsDailyOrder::READY) {
            return;
        }

        $order->update([
            'status' => CorsDailyOrder::FAILED,
            'failure_reason' => Str::limit($exception?->getMessage() ?? 'Preparation failed.', 500),
        ]);

        if (! $order->user) {
            return;
        }

        try {
            Mail::to($order->user)->send(new CorsDailyOrderFailed($order));
        } catch (Throwable $mailException) {
            report($mailException);
        }
    }
}
