<?php

namespace App\Console\Commands;

use App\Models\CorsDailyOrder;
use App\Services\CorsDailyOrderService;
use App\Services\PaystackGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcileCorsDailyPayments extends Command
{
    protected $signature = 'cors:reconcile-daily-payments';

    protected $description = 'Confirm pending 24-hour orders that Paystack has already charged';

    public function handle(PaystackGateway $paystack, CorsDailyOrderService $orders): int
    {
        if (! $paystack->enabled()) {
            $this->info('Paystack is not configured. Skipped.');

            return self::SUCCESS;
        }

        $confirmed = 0;

        CorsDailyOrder::query()
            ->where('status', CorsDailyOrder::PENDING)
            ->whereNull('paid_at')
            ->whereNotNull('payment_reference')
            ->where('created_at', '>=', now()->subDay())
            ->eachById(function (CorsDailyOrder $order) use ($paystack, $orders, &$confirmed) {
                try {
                    $payment = $paystack->verify($order->payment_reference);
                } catch (Throwable $exception) {
                    Log::warning('Could not verify a pending 24-hour payment.', [
                        'order_id' => $order->id,
                        'reference' => $order->payment_reference,
                        'message' => $exception->getMessage(),
                    ]);

                    return;
                }

                if (! $orders->paymentMatches($order, $payment)) {
                    return;
                }

                $orders->markPaid($order, (string) ($payment['channel'] ?? 'paystack'));
                $confirmed++;
            });

        $this->info("Confirmed {$confirmed} pending 24-hour payment(s).");

        return self::SUCCESS;
    }
}
