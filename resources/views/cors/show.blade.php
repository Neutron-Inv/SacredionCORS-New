@extends('layouts.main')

@section('title', $station->name.' — SOGPOS')

@section('content')
    {{-- HERO --}}
    <section class="relative pt-24 pb-10 border-b border-slate-200 overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-bg.jpg') }}" alt="" class="w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-[#dcebf5]/95 via-[#eaf3fa]/85 to-[#f3f8fc]/55"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#e9f3fa]/70 via-transparent to-[#f0f7fc]/35"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-6">
            <a href="{{ route('home') }}#cors" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-brand-700 transition">
                ← Back to CORS network
            </a>

            <div class="mt-6 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 border border-brand-100">{{ $station->code }}</span>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Live</span>
                        <span class="text-xs text-slate-500">{{ $station->region }}</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-slate-900">{{ $station->hero_text_display }}</h1>
                    <p class="mt-3 text-slate-600 text-base">{{ $station->location }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $station->state_name }} · {{ $station->provider }}</p>

                    @if ($station->partner_logo_primary_url || $station->partner_logo_secondary_url)
                        <div class="mt-5 flex items-center gap-3">
                            @if ($station->partner_logo_primary_url)
                                <div class="h-11 px-3 rounded-xl border border-white/60 bg-white/85 backdrop-blur-sm shadow-sm flex items-center">
                                    <img src="{{ $station->partner_logo_primary_url }}" alt="{{ $station->name }} partner logo 1" class="h-7 w-auto object-contain">
                                </div>
                            @endif
                            @if ($station->partner_logo_secondary_url)
                                <div class="h-11 px-3 rounded-xl border border-white/60 bg-white/85 backdrop-blur-sm shadow-sm flex items-center">
                                    <img src="{{ $station->partner_logo_secondary_url }}" alt="{{ $station->name }} partner logo 2" class="h-7 w-auto object-contain">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 text-sm">
                    <div class="rounded-xl border border-slate-200 px-4 py-3 bg-white/90 backdrop-blur-sm shadow-sm">
                        <div class="text-xs text-slate-500">Available files</div>
                        <div class="font-semibold text-slate-900">{{ number_format($files->total()) }}</div>
                    </div>
                    @if ($station->last_synced_at)
                        <div class="rounded-xl border border-slate-200 px-4 py-3 bg-white/90 backdrop-blur-sm shadow-sm">
                            <div class="text-xs text-slate-500">Last synced</div>
                            <div class="font-semibold text-slate-900">{{ $station->last_synced_at->diffForHumans() }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="py-10">
        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-3 gap-8">
            {{-- DATA TABLE --}}
            <div class="lg:col-span-2" x-data="{ selected: [] }">
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-lg">GNSS data</h2>
                            <p class="text-sm text-slate-500 mt-0.5">Search, filter and download RINEX observations.</p>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('cors.download-zip', $station) }}" x-show="selected.length > 0" x-cloak>
                                @csrf
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="file_ids[]" :value="id">
                                </template>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">
                                    Download selected (<span x-text="selected.length"></span>)
                                </button>
                            </form>
                        @endauth
                    </div>

                    <form method="GET" class="p-5 border-b border-slate-100 grid sm:grid-cols-3 gap-3">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search filename"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <select name="year" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                            <option value="">All years</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <select name="month" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                                <option value="">All months</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(request('month') == $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                            <button class="px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-50">Filter</button>
                        </div>
                    </form>

                    @guest
                        <div class="px-5 py-3 bg-amber-50 border-b border-amber-100 text-sm text-amber-800">
                            <a href="{{ route('login') }}" class="font-medium underline">Sign in</a> or
                            <a href="{{ route('register') }}" class="font-medium underline">register</a> to download GNSS files.
                        </div>
                    @endguest

                    @if ($files->isEmpty())
                        <div class="p-12 text-center">
                            <p class="text-slate-600 font-medium">No GNSS files indexed yet</p>
                            <p class="text-sm text-slate-400 mt-2 max-w-md mx-auto">
                                Files are synced from the station FTP feed. An administrator can run
                                <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">php artisan cors:sync {{ $station->slug }}</code>
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        @auth<th class="w-10 p-3"></th>@endauth
                                        <th class="text-left p-3 font-medium">Data name</th>
                                        <th class="text-left p-3 font-medium">Date</th>
                                        <th class="text-left p-3 font-medium">Size</th>
                                        <th class="text-right p-3 font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($files as $file)
                                        <tr class="hover:bg-slate-50/80">
                                            @auth
                                                <td class="p-3">
                                                    <input type="checkbox" value="{{ $file->id }}"
                                                        class="rounded border-slate-300 text-brand-700 focus:ring-brand-500"
                                                        @change="selected.includes('{{ $file->id }}') ? selected = selected.filter(i => i !== '{{ $file->id }}') : selected.push('{{ $file->id }}')">
                                                </td>
                                            @endauth
                                            <td class="p-3">
                                                <div class="font-medium text-slate-900">{{ $file->download_name }}</div>
                                                <div class="text-xs text-slate-400">{{ strtoupper($file->ext ?? 'file') }}</div>
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ $file->observed_at?->format('M j, Y') ?? '—' }}
                                            </td>
                                            <td class="p-3 text-slate-600">{{ $file->formatted_size }}</td>
                                            <td class="p-3 text-right">
                                                @auth
                                                    <a href="{{ route('cors.files.download', $file) }}"
                                                        class="inline-flex px-3 py-1.5 rounded-lg text-brand-700 hover:bg-brand-50 font-medium transition">
                                                        Download
                                                    </a>
                                                @else
                                                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-brand-700">Login</a>
                                                @endauth
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-slate-100">
                            {{ $files->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- LOCATION --}}
            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="font-semibold">Station location</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $station->location }}</p>
                    @if ($station->latitude && $station->longitude)
                        <p class="mt-3 text-xs text-slate-400">
                            {{ number_format($station->latitude, 6) }}, {{ number_format($station->longitude, 6) }}
                        </p>
                    @endif
                </div>

                @if ($station->latitude && $station->longitude)
                    <div class="rounded-2xl border border-slate-200 overflow-hidden">
                        <div id="station-map" class="h-72 w-full"></div>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-brand-50 p-5 text-sm">
                    <h3 class="font-semibold text-brand-900">Need access?</h3>
                    <p class="mt-2 text-brand-800/80 leading-relaxed">
                        Register for a plan to download unlimited GNSS observations from this station.
                    </p>
                    <a href="{{ route('home') }}#pricing" class="inline-flex mt-4 px-4 py-2 rounded-lg bg-brand-700 text-white font-medium hover:bg-brand-900 transition">
                        View plans
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@if ($station->latitude && $station->longitude)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('station-map', { scrollWheelZoom: false }).setView(
                [{{ $station->latitude }}, {{ $station->longitude }}], 13
            );

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                maxZoom: 18
            }).addTo(map);

            L.marker([{{ $station->latitude }}, {{ $station->longitude }}])
                .addTo(map)
                .bindPopup(@json($station->name))
                .openPopup();
        });
    </script>
    @endpush
@endif
