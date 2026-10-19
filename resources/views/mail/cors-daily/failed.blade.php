<x-mail::message>
# We could not prepare your file

We could not finish the 24-hour file for **{{ $order->station->name }}** on **{{ $order->observed_on->format('F j, Y') }}**.

Open the station page and order that day again. You will not be charged a second time for this attempt. If it fails again, reply to this email and we will help.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
