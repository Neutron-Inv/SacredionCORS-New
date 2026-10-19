<x-mail::message>
# Your 24-hour file is being prepared

We received payment for **{{ $order->station->name }}** on **{{ $order->observed_on->format('F j, Y') }}**.

{{ number_format($order->file_count) }} observation {{ \Illuminate\Support\Str::plural('file', $order->file_count) }} will be merged into one daily file per type, then zipped. We will email you a download link when it is ready.

The link stays available for {{ (int) config('cors.daily.download_days') }} days. After that, the file is deleted from our server.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
