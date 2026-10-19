<?php

namespace App\Services;

use App\Jobs\PrepareCorsDailyArchive;
use App\Mail\CorsDailyOrderPreparing;
use App\Models\CorsDailyOrder;
use App\Models\CorsStation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CorsDailyOrderService
{
    public function __construct(
        private CorsDailyCatalog $catalog,
        private PaystackGateway $paystack,
    ) {}

    public function paymentMode(): string
    {
        if ((float) config('cors.daily.price') <= 0) {
            return 'unavailable';
        }

        if ($this->paystack->enabled()) {
            return 'paystack';
        }

        if (app()->environment('local')) {
            return 'local';
        }

        return 'unavailable';
    }

    public function priceLabel(): string
    {
        $amount = (float) config('cors.daily.price');
        $currency = strtoupper((string) config('cors.daily.currency', 'NGN'));
        $formatted = fmod($amount, 1.0) === 0.0
            ? number_format($amount)
            : number_format($amount, 2);

        return $currency === 'NGN' ? '₦'.$formatted : $currency.' '.$formatted;
    }

    public function amountMinor(): int
    {
        return (int) round(((float) config('cors.daily.price')) * 100);
    }

    public function currency(): string
    {
        return strtoupper((string) config('cors.daily.currency', 'NGN'));
    }

    /**
     * @return array{order: CorsDailyOrder, payment: ?array<string, string>, reused: bool}
     */
    public function start(User $user, CorsStation $station, string $date, bool $refresh = false): array
    {
        if ($this->paymentMode() === 'unavailable') {
            throw ValidationException::withMessages([
                'payment' => '24-hour ordering is not available right now.',
            ]);
        }

        $observedOn = $this->catalog->parseOrderableDate($date);
        $summary = $this->catalog->summary($station, $observedOn->toDateString());

        $result = DB::transaction(function () use ($user, $station, $observedOn, $summary, $refresh) {
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $existing = CorsDailyOrder::query()
                ->where('user_id', $user->id)
                ->where('cors_station_id', $station->id)
                ->whereDate('observed_on', $observedOn->toDateString())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $downloadStillOpen = $existing
                && $existing->paid_at
                && ! in_array($existing->status, [CorsDailyOrder::EXPIRED, CorsDailyOrder::FAILED], true)
                && ! ($existing->expires_at && $existing->expires_at->isPast());

            if ($downloadStillOpen) {
                return ['order' => $existing, 'reused' => true];
            }

            if ($existing?->status === CorsDailyOrder::FAILED && $existing->paid_at) {
                $existing->update([
                    'status' => CorsDailyOrder::PAID,
                    'failure_reason' => null,
                    'file_count' => $summary['count'],
                ]);

                return ['order' => $existing, 'reused' => true, 'retry' => true];
            }

            $order = $existing?->status === CorsDailyOrder::PENDING
                ? $existing
                : new CorsDailyOrder([
                    'user_id' => $user->id,
                    'cors_station_id' => $station->id,
                    'download_token' => Str::random(48),
                ]);

            if (! $order->exists || $refresh || ! $order->payment_reference) {
                $order->payment_reference = $this->reference();
                $order->payment_access_code = null;
            }

            $order->fill([
                'observed_on' => $observedOn->toDateString(),
                'file_count' => $summary['count'],
                'amount' => $this->amountMinor(),
                'currency' => $this->currency(),
                'status' => CorsDailyOrder::PENDING,
            ]);
            $order->save();

            return ['order' => $order, 'reused' => false];
        });

        /** @var CorsDailyOrder $order */
        $order = $result['order'];

        if (! empty($result['retry'])) {
            PrepareCorsDailyArchive::dispatch($order->id);

            return ['order' => $order->refresh(), 'payment' => null, 'reused' => true];
        }

        if ($result['reused']) {
            return ['order' => $order, 'payment' => null, 'reused' => true];
        }

        if ($this->paymentMode() === 'local') {
            return ['order' => $order, 'payment' => null, 'reused' => false];
        }

        $order->setRelation('station', $station);
        $order->setRelation('user', $user);

        if (! $order->payment_access_code) {
            $payment = $this->paystack->initialize($order, $user->email);
            $order->update(['payment_access_code' => $payment['access_code']]);
        } else {
            $payment = [
                'access_code' => $order->payment_access_code,
                'authorization_url' => '',
                'reference' => $order->payment_reference,
            ];
        }

        return ['order' => $order->refresh(), 'payment' => $payment, 'reused' => false];
    }

    public function markPaid(CorsDailyOrder $order, string $channel): CorsDailyOrder
    {
        $shouldStart = false;

        $order = DB::transaction(function () use ($order, $channel, &$shouldStart) {
            $locked = CorsDailyOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->paid_at) {
                return $locked;
            }

            $locked->forceFill([
                'status' => CorsDailyOrder::PAID,
                'payment_channel' => $channel,
                'paid_at' => now(),
            ])->save();

            $shouldStart = true;

            return $locked;
        });

        if (! $shouldStart) {
            return $order;
        }

        $order->loadMissing(['user', 'station']);

        try {
            Mail::to($order->user)->send(new CorsDailyOrderPreparing($order));
        } catch (Throwable $exception) {
            report($exception);
        }

        PrepareCorsDailyArchive::dispatch($order->id);

        return $order->refresh();
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    public function paymentMatches(CorsDailyOrder $order, array $payment): bool
    {
        return ($payment['status'] ?? null) === 'success'
            && (int) ($payment['amount'] ?? 0) === (int) $order->amount
            && strtoupper((string) ($payment['currency'] ?? '')) === $order->currency;
    }

    private function reference(): string
    {
        return 'CORS-'.Str::upper((string) Str::ulid());
    }
}
