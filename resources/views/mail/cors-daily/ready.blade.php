@php
    $expiresAt = $order->expires_at?->timezone(config('cors.daily.timezone'))->format('M j, Y g:i A');
    $recipient = $order->user->first_name ?: $order->user->name;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your 24-hour CORS file is ready</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;color:#0f172a;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        The merged file for {{ $order->station->name }} is ready. The link expires {{ $expiresAt }}.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e8eef3;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#050094;padding:28px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#ffffff;border-radius:10px;padding:6px;">
                                        <img src="{{ asset('images/slogo.png') }}" alt="Sacredion" width="44" height="44" style="display:block;width:44px;height:44px;border:0;border-radius:8px;">
                                    </td>
                                    <td style="padding-left:14px;">
                                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:18px;line-height:22px;font-weight:700;color:#ffffff;">SOGPOS</p>
                                        <p style="margin:2px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:16px;color:#d8daff;">Sacredion CORS</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 8px;font-family:Arial,Helvetica,sans-serif;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:18px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#050094;">Download ready</p>
                            <h1 style="margin:0;font-size:26px;line-height:32px;font-weight:700;color:#0f172a;">Your 24-hour file is ready</h1>
                            <p style="margin:16px 0 0;font-size:15px;line-height:24px;color:#334155;">
                                Hello {{ $recipient }}, the merged file for <strong style="color:#0f172a;">{{ $order->station->name }}</strong> on <strong style="color:#0f172a;">{{ $order->observed_on->format('F j, Y') }}</strong> is ready to download.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef0ff;border:1px solid #d8daff;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:22px;color:#334155;">
                                        <strong style="color:#030062;">{{ $order->station->name }}</strong><br>
                                        {{ $order->observed_on->format('l, F j, Y') }}<br>
                                        {{ number_format($order->file_count) }} observation {{ \Illuminate\Support\Str::plural('file', $order->file_count) }} merged into one daily file per type
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="padding:28px 32px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#050094;border-radius:10px;">
                                        <a href="{{ $order->download_url }}" style="display:inline-block;padding:14px 22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:20px;font-weight:700;color:#ffffff;text-decoration:none;">Download 24-hour file</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 28px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:21px;color:#64748b;">
                            This link works until {{ $expiresAt }} ({{ config('cors.daily.timezone') }}). After that, the file is deleted from our server.
                            If the button does not open, copy this address into your browser:<br>
                            <a href="{{ $order->download_url }}" style="color:#050094;word-break:break-all;">{{ $order->download_url }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 28px;background:#f8fafc;border-top:1px solid #e8eef3;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:19px;color:#64748b;">
                            <strong style="color:#0f172a;">Sacredion Nigeria Limited</strong><br>
                            2nd Floor, Alphon House, 50/52 Toyin Street, Ikeja, Lagos State, Nigeria.<br>
                            +234 (0) 808 085 2923 · <a href="mailto:info@sacredioncors.com" style="color:#050094;text-decoration:none;">info@sacredioncors.com</a><br>
                            <a href="{{ route('privacy') }}" style="color:#050094;text-decoration:none;">Privacy Policy</a>
                            <p style="margin:12px 0 0;">© {{ date('Y') }} Sacredion Nigeria Limited. SOGPOS — high-precision CORS data across Nigeria.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
