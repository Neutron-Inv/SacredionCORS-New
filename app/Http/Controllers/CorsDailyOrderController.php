<?php

namespace App\Http\Controllers;

use App\Models\CorsDailyOrder;
use App\Models\CorsStation;
use App\Services\CorsDailyCatalog;
use App\Services\CorsDailyOrderService;
use App\Services\PaystackGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CorsDailyOrderController extends Controller
{
    public function preview(Request $request, CorsStation $station, CorsDailyCatalog $catalog)
    {
        $this->guardStation($station);

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        return response()->json($catalog->summary($station, $validated['date']));
    }

    public function store(Request $request, CorsStation $station, CorsDailyOrderService $orders)
    {
        $this->guardStation($station);

        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'refresh' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $orders->start(
                $request->user(),
                $station,
                $request->string('date')->toString(),
                $request->boolean('refresh'),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Could not start payment. Please try again.',
            ], 502);
        }

        /** @var CorsDailyOrder $order */
        $order = $result['order'];

        if ($result['reused']) {
            return response()->json($this->existingPayload($order));
        }

        return response()->json([
            'mode' => $orders->paymentMode(),
            'confirm_url' => route('cors.daily.confirm', $order),
            'access_code' => $result['payment']['access_code'] ?? null,
            'file_count' => $order->file_count,
            'price_label' => $order->formatted_amount,
        ]);
    }

    public function confirm(Request $request, CorsDailyOrder $order, CorsDailyOrderService $orders, PaystackGateway $paystack)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->paid_at) {
            return response()->json($this->paidPayload($order));
        }

        if ($orders->paymentMode() === 'local') {
            abort_unless(app()->environment('local'), 403);
            $order = $orders->markPaid($order, 'local');

            return response()->json($this->paidPayload($order));
        }

        try {
            $payment = $paystack->verify($order->payment_reference);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Could not verify the payment. Please try again.',
            ], 502);
        }

        if (! $orders->paymentMatches($order, $payment)) {
            return response()->json([
                'message' => 'Payment was not completed for this order.',
            ], 422);
        }

        $order = $orders->markPaid($order, (string) ($payment['channel'] ?? 'paystack'));

        return response()->json($this->paidPayload($order));
    }

    public function webhook(Request $request, PaystackGateway $paystack, CorsDailyOrderService $orders)
    {
        abort_unless($paystack->enabled(), 404);

        $payload = $request->getContent();

        abort_unless($paystack->signatureIsValid($payload, $request->header('x-paystack-signature')), 400);

        $reference = $request->json('data.reference');

        if ($request->json('event') !== 'charge.success' || ! is_string($reference) || $reference === '') {
            return response()->json(['ok' => true]);
        }

        $order = CorsDailyOrder::query()->where('payment_reference', $reference)->first();

        if (! $order || $order->paid_at) {
            return response()->json(['ok' => true]);
        }

        try {
            $payment = $paystack->verify($reference);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['ok' => false], 500);
        }

        if ($orders->paymentMatches($order, $payment)) {
            $orders->markPaid($order, (string) ($payment['channel'] ?? 'paystack'));
        }

        return response()->json(['ok' => true]);
    }

    public function download(string $token): StreamedResponse
    {
        $order = CorsDailyOrder::query()->where('download_token', $token)->firstOrFail();

        if ($order->status === CorsDailyOrder::EXPIRED || ($order->expires_at && $order->expires_at->isPast())) {
            abort(410, 'This download link has expired.');
        }

        abort_unless($order->isDownloadable() && $order->archive_path, 404);
        abort_unless(Storage::disk('local')->exists($order->archive_path), 404);

        $filename = $order->archive_name ?: 'cors-24h.zip';
        $filename = str_replace(['"', '\\', '/'], '', $filename);

        return Storage::disk('local')->download($order->archive_path, $filename);
    }

    private function guardStation(CorsStation $station): void
    {
        abort_unless($station->is_active && ! $station->is_external, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function existingPayload(CorsDailyOrder $order): array
    {
        $ready = $order->isDownloadable();

        return [
            'mode' => $ready ? 'ready' : 'processing',
            'message' => $ready
                ? 'This day is already prepared. The download link is below and in your email.'
                : 'This day is already being prepared. We will email you when the file is ready.',
            'download_url' => $ready ? $order->download_url : null,
            'file_count' => $order->file_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paidPayload(CorsDailyOrder $order): array
    {
        $ready = $order->isDownloadable();

        return [
            'status' => $order->status,
            'message' => $ready
                ? 'Your 24-hour file is ready. We also emailed the download link.'
                : 'Payment received. Your file is being prepared. We will email you when it is ready.',
            'download_url' => $ready ? $order->download_url : null,
        ];
    }
}
