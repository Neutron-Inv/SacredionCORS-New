@extends('layouts.main')

@section('title', 'Sacredion CORS — SOGPOS')

@section('content')
    {{-- HERO --}}
    <section class="relative min-h-[88vh] flex flex-col justify-end overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-bg.jpg') }}" alt="" class="w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-white via-white/88 to-white/35"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-white/40"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-6 w-full pt-32 pb-12">
            <div class="max-w-xl" data-aos="fade-up">
                <p class="text-[13px] font-medium text-brand-600 tracking-wide">GNSS · CORS · Nigeria</p>
                <h1 class="mt-4 text-[2.15rem] sm:text-5xl font-semibold tracking-tight leading-[1.15] text-slate-900">
                    Sacredion Online GNSS Positioning Service
                </h1>
                <p class="mt-2 text-lg text-slate-500 font-medium">(SOGPOS)</p>
                <p class="mt-5 text-slate-600 leading-relaxed">
                    Gathering all GNSS data you need in one place. Centimetre-level CORS access across Nigeria, around the
                    clock.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#cors"
                        class="px-5 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">
                        Seek data
                    </a>
                    <a href="#pricing"
                        class="px-5 py-2.5 rounded-lg border border-slate-200 bg-white/80 backdrop-blur-sm text-sm font-medium text-slate-700 hover:bg-white transition">
                        Pricing
                    </a>
                </div>
            </div>

            <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-px bg-slate-200 rounded-xl overflow-hidden border border-slate-200 shadow-soft"
                data-aos="fade-up">
                @foreach ([[$states->count(), 'CORS stations'], ['24/7', 'Live coverage'], ['cm-level', 'Accuracy'], ['NTRIP', 'RTK ready']] as $stat)
                    <div class="bg-white/90 backdrop-blur-sm px-5 py-6 text-center">
                        <div class="text-xl font-semibold text-slate-900">{{ $stat[0] }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $stat[1] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @php
        $corsTones = [
            [
                'bg' => 'bg-sky-50',
                'text' => 'text-sky-700',
                'ring' => 'group-hover:ring-sky-200',
                'bar' => 'bg-sky-500',
            ],
            [
                'bg' => 'bg-teal-50',
                'text' => 'text-teal-700',
                'ring' => 'group-hover:ring-teal-200',
                'bar' => 'bg-teal-500',
            ],
            [
                'bg' => 'bg-indigo-50',
                'text' => 'text-indigo-700',
                'ring' => 'group-hover:ring-indigo-200',
                'bar' => 'bg-indigo-500',
            ],
            [
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'ring' => 'group-hover:ring-amber-200',
                'bar' => 'bg-amber-500',
            ],
            [
                'bg' => 'bg-rose-50',
                'text' => 'text-rose-700',
                'ring' => 'group-hover:ring-rose-200',
                'bar' => 'bg-rose-400',
            ],
            [
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'ring' => 'group-hover:ring-emerald-200',
                'bar' => 'bg-emerald-500',
            ],
            [
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-700',
                'ring' => 'group-hover:ring-violet-200',
                'bar' => 'bg-violet-500',
            ],
            [
                'bg' => 'bg-cyan-50',
                'text' => 'text-cyan-700',
                'ring' => 'group-hover:ring-cyan-200',
                'bar' => 'bg-cyan-500',
            ],
        ];
        $regions = $states
            ->pluck('region')
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        $stationsByState = $states
            ->groupBy('state_name')
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn($group) => $group->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values());
        $stateCount = $stationsByState->count();
    @endphp

    {{-- MOBILE APP --}}
    <section id="mobile-app" class="py-8">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-0 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-900 via-brand-700 to-brand-600 text-white"
                data-aos="fade-up">
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <p class="text-sm font-medium text-brand-100">Live RTK in the field</p>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight">SOGPOS Mobile</h2>
                    <p class="mt-4 text-brand-50/90 leading-relaxed max-w-lg">
                        Download the SOGPOS Mobile app on Google Play to access live RTK corrections across the Sacredion
                        CORS network — wherever your survey takes you.
                    </p>

                    <ul class="mt-6 space-y-3 text-sm text-brand-50/95">
                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center shrink-0">✓</span>
                            <span>Live RTK/NTRIP access across {{ $states->count() }} CORS stations nationwide</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center shrink-0">✓</span>
                            <span>Centimetre-level positioning for cadastral and engineering surveys</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span
                                class="mt-0.5 w-5 h-5 rounded-full bg-white/15 flex items-center justify-center shrink-0">✓</span>
                            <span>Built for field crews — connect, fix, and work with confidence</span>
                        </li>
                    </ul>

                    <div class="mt-8">
                        @php($playStoreUrl = config('services.sogpos.google_play_url'))
                        @if ($playStoreUrl)
                            <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer"
                                class="inline-block transition hover:opacity-90 hover:scale-[1.02]">
                                <img src="{{ asset('images/google-play-badge.png') }}"
                                    alt="Get it on Google Play" width="180" height="53" class="h-[53px] w-auto">
                            </a>
                        @else
                            <img src="{{ asset('images/google-play-badge.png') }}" alt="Get it on Google Play"
                                width="180" height="53" class="h-[53px] w-auto opacity-90">
                        @endif
                    </div>
                </div>

                <div class="relative min-h-[320px] bg-brand-900/40 flex items-center justify-center p-8 md:p-12">
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.12),transparent_55%)]">
                    </div>
                    <div class="relative w-full max-w-xs">
                        <div class="rounded-[2rem] border border-white/20 bg-slate-950/40 backdrop-blur-sm p-4 shadow-2xl">
                            <div
                                class="rounded-[1.5rem] overflow-hidden border border-white/10 bg-slate-950 aspect-[9/16]">
                                <img src="{{ asset('images/display.jpeg') }}" alt="SOGPOS Mobile app screenshot"
                                    class="w-full h-full object-cover object-top">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CORS --}}
    <section id="cors" class="py-20" x-data="{ q: '', region: 'all', open: null }">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8" data-aos="fade-up">
                <div>
                    <p class="text-sm font-medium text-brand-600">{{ $states->count() }} stations · {{ $stateCount }}
                        states</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight">Pick a CORS station</h2>
                    <p class="mt-2 text-slate-500 max-w-xl">Choose a state, then open the station you need for GNSS
                        observations.</p>
                </div>
                <div class="relative w-full lg:w-80">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <input x-model="q" type="search" placeholder="Search state or station"
                        class="w-full rounded-full border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-8" data-aos="fade-up">
                <button type="button" @click="region = 'all'"
                    :class="region === 'all' ? 'bg-brand-700 text-white border-brand-700' :
                        'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                    class="px-3.5 py-1.5 rounded-full text-sm border transition">
                    All regions
                </button>
                @foreach ($regions as $region)
                    <button type="button" @click="region = '{{ $region }}'"
                        :class="region === '{{ $region }}' ? 'bg-brand-700 text-white border-brand-700' :
                            'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                        class="px-3.5 py-1.5 rounded-full text-sm border transition">
                        {{ $region }}
                    </button>
                @endforeach
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 items-start">
                @foreach ($stationsByState as $stateName => $group)
                    @php
                        $tone = $corsTones[$loop->index % count($corsTones)];
                        $stateKey = \Illuminate\Support\Str::slug($stateName);
                        $stateRegion = $group->first()->region;
                        $stateLabel = strtoupper(substr(preg_replace('/\s*(State|\/.*)$/i', '', $stateName), 0, 2));
                        $stateSearch = addslashes(
                            strtolower(
                                $stateName .
                                    ' ' .
                                    $stateRegion .
                                    ' ' .
                                    $group->pluck('name')->implode(' ') .
                                    ' ' .
                                    $group->pluck('location')->implode(' ') .
                                    ' ' .
                                    $group->pluck('code')->implode(' '),
                            ),
                        );
                    @endphp
                    <div x-show="(region === 'all' || region === '{{ $stateRegion }}') && '{{ $stateSearch }}'.includes(q.toLowerCase())"
                        data-aos="fade-up" data-aos-delay="{{ ($loop->index % 4) * 40 }}"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white ring-1 ring-transparent {{ $tone['ring'] }} transition duration-200"
                        :class="open === '{{ $stateKey }}' || q.length ? 'shadow-soft' :
                            'hover:-translate-y-1 hover:shadow-soft'">
                        <span class="absolute inset-x-0 top-0 h-1 {{ $tone['bar'] }} opacity-80"></span>

                        <button type="button"
                            @click="open = open === '{{ $stateKey }}' ? null : '{{ $stateKey }}'"
                            class="w-full text-left p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div
                                    class="w-12 h-12 rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center font-semibold tracking-wide">
                                    {{ $stateLabel }}
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-1 rounded-full">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span
                                            class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 animate-ping opacity-60"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    </span>
                                    Live
                                </span>
                            </div>

                            <h3 class="mt-4 text-[15px] font-semibold text-slate-900">{{ $stateName }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $group->count() }}
                                {{ \Illuminate\Support\Str::plural('station', $group->count()) }}</p>
                            <p class="mt-3 text-[11px] uppercase tracking-wider text-slate-400">{{ $stateRegion }}</p>

                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                                <span class="font-medium text-brand-700">
                                    <span x-show="open !== '{{ $stateKey }}' && !q.length">View stations</span>
                                    <span x-show="open === '{{ $stateKey }}' || q.length" x-cloak>Hide stations</span>
                                </span>
                                <span
                                    class="w-7 h-7 rounded-full {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center transition"
                                    :class="open === '{{ $stateKey }}' || q.length ? 'rotate-90' :
                                        'group-hover:translate-x-0.5'">→</span>
                            </div>
                        </button>

                        <div x-show="open === '{{ $stateKey }}' || q.length > 0" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0" class="px-3 pb-3">
                            <div class="space-y-2">
                                @foreach ($group as $station)
                                    <a href="{{ $station->public_url }}"
                                        @if ($station->is_external) target="_blank" rel="noopener" @endif
                                        x-show="!q || '{{ addslashes(strtolower($stateName)) }}'.includes(q.toLowerCase()) || '{{ addslashes(strtolower($station->name . ' ' . $station->location . ' ' . $station->code . ' ' . $station->city)) }}'.includes(q.toLowerCase())"
                                        class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5 hover:border-slate-200 hover:bg-white hover:shadow-sm transition">
                                        <span
                                            class="w-9 h-9 rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-[11px] font-semibold tracking-wide shrink-0">
                                            {{ $station->code }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span
                                                class="block text-sm font-medium text-slate-900 truncate">{{ $station->name }}</span>
                                            <span
                                                class="block text-[11px] text-slate-500 truncate">{{ $station->location }}</span>
                                        </span>
                                        <span class="text-slate-400 text-sm">→</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SOFTWARE --}}
    <section class="py-8">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-0 overflow-hidden rounded-2xl border border-slate-200 bg-white"
                data-aos="fade-up">
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <p class="text-sm font-medium text-brand-600">Post-processing</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">Tersus Geomatics Office</h2>
                    <p class="mt-3 text-slate-500 leading-relaxed">
                        Registered users can download GNSS post-processing software free on this platform.
                    </p>
                    <div class="mt-7">
                        @auth
                            <a href="#"
                                class="inline-flex px-5 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">Download
                                now</a>
                        @else
                            <a href="{{ route('register') }}"
                                class="inline-flex px-5 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">Register
                                to download</a>
                        @endauth
                    </div>
                </div>
                <div class="relative min-h-[260px] bg-slate-100">
                    <img src="{{ asset('images/hero-bg.jpg') }}" alt="GNSS post processing"
                        class="absolute inset-0 w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    {{-- <section id="pricing" class="py-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight">Pricing</h2>
                <p class="mt-2 text-slate-500">Choose the plan that fits your survey programme.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
                @foreach ($plans as $plan)
                    <div data-aos="fade-up"
                        class="relative rounded-2xl border bg-white p-7 flex flex-col
                        {{ $plan->is_popular ? 'border-brand-600 shadow-soft' : 'border-slate-200' }}">
                        @if ($plan->is_popular)
                            <span class="absolute -top-2.5 left-6 px-2.5 py-0.5 bg-brand-700 text-white text-[11px] font-medium rounded-full">
                                Popular
                            </span>
                        @endif

                        <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $plan->description }}</p>

                        <div class="mt-6 mb-7">
                            <span class="text-3xl font-semibold tracking-tight">₦{{ number_format($plan->price) }}</span>
                            <span class="text-sm text-slate-400">/{{ $plan->billing_cycle }}</span>
                        </div>

                        <ul class="space-y-2.5 text-sm text-slate-600 mb-8 flex-1">
                            @foreach ($plan->features as $feature)
                                <li class="flex gap-2">
                                    <span class="text-brand-600">✓</span>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}"
                            class="block text-center py-2.5 rounded-lg text-sm font-medium transition
                            {{ $plan->is_popular ? 'bg-brand-700 text-white hover:bg-brand-900' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                            Register
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white px-7 py-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4" data-aos="fade-up">
                <div>
                    <h3 class="font-semibold">Start with a 10-day free trial</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Full access to every feature — no commitment.</p>
                </div>
                <a href="{{ route('register') }}" class="shrink-0 px-5 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">
                    Start free trial
                </a>
            </div>
        </div>
    </section> --}}

    {{-- ABOUT --}}
    <section id="about" class="py-8 pb-20">
        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-2 gap-10 items-center">
            <div class="rounded-2xl overflow-hidden border border-slate-200 h-[380px]" data-aos="fade-up">
                <img src="{{ asset('images/why-us.jpg') }}" alt="Sacredion geospatial infrastructure"
                    class="w-full h-full object-cover">
            </div>

            <div data-aos="fade-up" data-aos-delay="60">
                <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight">What is SOGPOS?</h2>
                <p class="mt-3 text-slate-500 leading-relaxed">
                    Developed in collaboration with industry and academic partners across Nigeria.
                </p>
                <ul class="mt-6 space-y-2.5 text-sm text-slate-600">
                    @foreach ($partners as $partner)
                        <li class="flex gap-2.5">
                            <span class="mt-2 w-1 h-1 rounded-full bg-brand-600 shrink-0"></span>
                            <span>{{ $partner }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-7 rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="font-medium text-slate-900">More about us</h3>
                    <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">
                        A cross-disciplinary team offering Geospatial, Engineering and IT infrastructure services.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- MAP --}}
    <section class="pb-24">
        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-5 gap-10 items-center">
            <div class="lg:col-span-2" data-aos="fade-up">
                <h2 class="text-2xl sm:text-3xl font-semibold tracking-tight">Where our CORS are located</h2>
                <p class="mt-3 text-slate-500 leading-relaxed">
                    {{ $states->count() }} stations across {{ $states->pluck('state_name')->unique()->implode(', ') }}.
                </p>
                <p class="mt-3 text-sm text-slate-400">Click a marker to identify a station.</p>
            </div>
            <div class="lg:col-span-3 border border-slate-200 rounded-2xl overflow-hidden" data-aos="fade-up">
                <div id="cors-map" class="h-[400px] w-full"></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stations = @json($states);
            const map = L.map('cors-map', {
                scrollWheelZoom: false
            }).setView([7.5, 6.5], 6);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                maxZoom: 18
            }).addTo(map);

            const icon = L.divIcon({
                className: '',
                html: '<div style="width:12px;height:12px;border-radius:999px;background:#050094;box-shadow:0 0 0 4px rgba(5,0,148,.18);border:2px solid #fff"></div>',
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            stations.forEach(function(s) {
                if (!s.latitude || !s.longitude) return;
                L.marker([s.latitude, s.longitude], {
                        icon: icon
                    })
                    .addTo(map)
                    .bindPopup('<strong>' + s.name + '</strong><br>' + (s.location || ''));
            });

            setTimeout(function() {
                map.invalidateSize();
            }, 400);
        });
    </script>
@endpush
