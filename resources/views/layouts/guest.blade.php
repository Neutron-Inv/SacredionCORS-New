<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sacredion CORS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900" style="font-family: Inter, ui-sans-serif, system-ui, sans-serif;">
    <div class="min-h-screen relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <img src="{{ asset('images/hero-bg.jpg') }}" alt="" class="w-full h-full object-cover opacity-20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(5,0,148,0.25),transparent_45%),radial-gradient(circle_at_80%_15%,rgba(3,0,98,0.2),transparent_45%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 via-slate-50/75 to-slate-50"></div>
        </div>

        <div class="relative max-w-6xl mx-auto min-h-screen px-6 py-10 flex items-center">
            <div class="w-full grid lg:grid-cols-2 gap-8 items-center">
                <div class="hidden lg:block pr-10">
                    <a href="/" class="inline-flex items-center gap-3">
                        <img src="{{ asset('images/slogo.png') }}" alt="Sacredion" class="w-12 h-12 object-contain">
                        <div>
                            <p class="font-semibold text-lg leading-tight">Sacredion CORS</p>
                            <p class="text-xs text-slate-500 tracking-wide uppercase">SOGPOS Platform</p>
                        </div>
                    </a>

                    <h1 class="mt-8 text-4xl font-semibold tracking-tight text-slate-900 leading-tight">
                        Precision GNSS data for a connected geospatial future.
                    </h1>
                    <p class="mt-4 text-slate-600 max-w-md">
                        Access trusted CORS stations, resilient download workflows, and modern infrastructure built for scale.
                    </p>

                    <div class="mt-8 flex items-center gap-3">
                        <div class="h-11 px-3 rounded-xl border border-slate-200 bg-white/90 flex items-center">
                            <img src="{{ asset('images/slogo.png') }}" alt="Sacredion logo" class="h-7 w-auto object-contain">
                        </div>
                        <div class="h-11 px-3 rounded-xl border border-slate-200 bg-white/90 flex items-center">
                            <img src="{{ asset('images/tlogo.png') }}" alt="Partner logo" class="h-7 w-auto object-contain">
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-xl mx-auto">
                    <div class="rounded-2xl border border-slate-200/90 bg-white/90 backdrop-blur-sm shadow-xl shadow-slate-200/60 p-6 sm:p-8">
                        <div class="lg:hidden mb-6">
                            <a href="/" class="inline-flex items-center gap-2.5">
                                <img src="{{ asset('images/slogo.png') }}" alt="Sacredion" class="w-10 h-10 object-contain">
                                <span class="font-semibold text-slate-900">Sacredion CORS</span>
                            </a>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
</body>
</html>
