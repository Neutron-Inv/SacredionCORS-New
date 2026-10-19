<?php

namespace App\Services;

use App\Models\CorsDailyOrder;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway
{
    public function enabled(): bool
    {
        return filled(config('services.paystack.public_key'))
            && filled(config('services.paystack.secret_key'));
    }

    /**
     * @return array{access_code: string, authorization_url: string, reference: string}
     */
    public function initialize(CorsDailyOrder $order, string $email): array
    {
        $response = Http::withToken($this->secret())
            ->acceptJson()
            ->timeout(20)
            ->post($this->url('/transaction/initialize'), [
                'email' => $email,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'reference' => $order->payment_reference,
                'metadata' => [
                    'order_id' => $order->id,
                    'station' => $order->station?->slug,
                    'observed_on' => $order->observed_on?->toDateString(),
                ],
            ]);

        $data = $response->json('data');

        if (! $response->ok() || ! $response->json('status') || empty($data['access_code'])) {
            throw new RuntimeException($response->json('message') ?: 'Could not start the payment.');
        }

        return [
            'access_code' => $data['access_code'],
            'authorization_url' => $data['authorization_url'] ?? '',
            'reference' => $data['reference'] ?? $order->payment_reference,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secret())
            ->acceptJson()
            ->timeout(20)
            ->get($this->url('/transaction/verify/'.rawurlencode($reference)));

        if (! $response->ok() || ! $response->json('status')) {
            throw new RuntimeException($response->json('message') ?: 'Could not verify the payment.');
        }

        return $response->json('data') ?? [];
    }

    public function signatureIsValid(string $payload, ?string $signature): bool
    {
        if (! $this->enabled() || ! is_string($signature) || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $this->secret()), $signature);
    }

    private function secret(): string
    {
        return (string) config('services.paystack.secret_key');
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.paystack.url'), '/').$path;
    }
}
