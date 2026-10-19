<x-mail::message>
# Your 24-hour file is ready

The merged file for **{{ $order->station->name }}** on **{{ $order->observed_on->format('F j, Y') }}** is ready to download.

<x-mail::button :url="$order->download_url">
Download 24-hour file
</x-mail::button>

This link works until {{ $order->expires_at?->timezone(config('cors.daily.timezone'))->format('M j, Y g:i A') }} ({{ config('cors.daily.timezone') }}). After that, the file is deleted from our server.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
