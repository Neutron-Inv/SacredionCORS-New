<?php

namespace App\Console\Commands;

use App\Models\CorsDailyOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredCorsDailyOrders extends Command
{
    protected $signature = 'cors:purge-daily-orders';

    protected $description = 'Delete 24-hour CORS archives whose download window has ended';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $purged = 0;

        CorsDailyOrder::query()
            ->where('status', CorsDailyOrder::READY)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->eachById(function (CorsDailyOrder $order) use ($disk, &$purged) {
                if ($order->archive_path) {
                    $disk->delete($order->archive_path);
                }

                $order->update([
                    'status' => CorsDailyOrder::EXPIRED,
                    'archive_path' => null,
                    'archive_size' => null,
                ]);

                $purged++;
            });

        $this->info("Purged {$purged} expired 24-hour archive(s).");

        return self::SUCCESS;
    }
}
