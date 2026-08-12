<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SOGPOS – Sacredion GNSS')</title>
    <meta name="description" content="Sacredion Online GNSS Positioning Service (SOGPOS) — high-precision CORS data across Nigeria.">
    <meta name="keywords" content="geospatial, GNSS, CORS, Sacredion, mapping, SOGPOS">
    <meta property="og:title" content="Sacredion CORS — SOGPOS">
    <meta property="og:description" content="All the GNSS data you need for CORS across Nigeria.">
    <meta property="og:image" content="{{ asset('images/slogo.png') }}">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#EEF5FA',
                            100: '#D6E8F4',
                            500: '#2B7EB5',
                            600: '#1E6A9A',
                            700: '#1E4356',
                            900: '#123040'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 1px 2px rgba(16, 24, 40, 0.04), 0 8px 24px rgba(16, 24, 40, 0.06)'
                    }
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        [x-cloak] { display: none !important; }

        body {
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            color: #0f172a;
            background: #f7f9fc;
        }

        .nav-scrolled {
            background: rgba(255, 255, 255, 0.92) !important;
            border-bottom-color: #e8eef3 !important;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
        }

        .leaflet-container {
            background: #eef4f8;
            font-family: inherit;
            border-radius: 16px;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, 0.1);
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

        .back-to-top {
            opacity: 0;
            pointer-events: none;
            transform: translateY(8px);
            transition: all 0.2s ease;
        }

        .back-to-top.show {
            opacity: 1;
            pointer-events: auto;
            transform: none;
        }
    </style>

    @stack('styles')
</head>

<body class="antialiased overflow-x-hidden text-slate-900" x-data="{ mobileOpen: false }">

    <header id="site-header" class="fixed top-0 inset-x-0 z-50 border-b border-transparent bg-white/80 backdrop-blur-md transition-all duration-200">
        <div class="max-w-6xl mx-auto px-5 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                <img src="{{ asset('images/slogo.png') }}" alt="Sacredion" class="h-9 w-9 object-contain rounded-lg">
                <span class="font-semibold tracking-tight text-slate-900">SOGPOS</span>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-[13.5px] text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-brand-700 transition">Home</a>
                <a href="#cors" class="hover:text-brand-700 transition">CORS</a>
                <a href="#pricing" class="hover:text-brand-700 transition">Pricing</a>
                <a href="#about" class="hover:text-brand-700 transition">About</a>
                <a href="#contact" class="hover:text-brand-700 transition">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3.5 py-2 text-[13.5px] text-slate-600 hover:text-brand-700">
                            {{ Auth::user()->name }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="px-3.5 py-2 text-[13.5px] text-slate-500 hover:text-slate-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-2 text-[13.5px] text-slate-600 hover:text-brand-700">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-[13.5px] font-medium rounded-lg bg-brand-700 text-white hover:bg-brand-900 transition">
                            Sign up
                        </a>
                    @endauth
                </div>

                <img src="{{ asset('images/tlogo.png') }}" alt="Tersus GNSS" title="Tersus GNSS" class="h-9 w-auto object-contain mix-blend-multiply">

                <button type="button" class="md:hidden p-2 rounded-lg text-slate-700 hover:bg-slate-100" @click="mobileOpen = !mobileOpen" aria-label="Toggle menu">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-slate-100 bg-white">
            <div class="px-5 py-4 space-y-3 text-sm text-slate-700">
                <a href="{{ route('home') }}" class="block" @click="mobileOpen=false">Home</a>
                <a href="#cors" class="block" @click="mobileOpen=false">CORS</a>
                <a href="#pricing" class="block" @click="mobileOpen=false">Pricing</a>
                <a href="#about" class="block" @click="mobileOpen=false">About</a>
                <a href="#contact" class="block" @click="mobileOpen=false">Contact</a>
                <div class="pt-2 flex gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex-1 text-center py-2 rounded-lg border border-slate-200">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="flex-1 text-center py-2 rounded-lg border border-slate-200">Login</a>
                        <a href="{{ route('register') }}" class="flex-1 text-center py-2 rounded-lg bg-brand-700 text-white font-medium">Sign up</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="relative">
        @yield('content')
    </main>

    <footer id="contact" class="border-t border-slate-200 bg-white">
        <div class="max-w-6xl mx-auto px-6 py-12">
            <div class="rounded-2xl bg-brand-50 border border-brand-100 px-6 py-7 flex flex-col md:flex-row md:items-center gap-5 justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Newsletter</h3>
                    <p class="text-sm text-slate-500 mt-1">Updates on instruments, stations and platform upgrades.</p>
                </div>
                <form class="flex w-full md:w-auto gap-2" onsubmit="event.preventDefault(); this.querySelector('[data-ok]').classList.remove('hidden');">
                    <input type="email" required placeholder="Email address"
                        class="flex-1 md:w-64 rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <button class="px-4 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">Subscribe</button>
                </form>
                <p data-ok class="hidden text-sm text-emerald-600">Thanks — you’re on the list.</p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-6 pb-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-10 text-sm">
            <div>
                <h4 class="font-semibold text-slate-900 mb-3">Links</h4>
                <ul class="space-y-2 text-slate-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700">Home</a></li>
                    <li><a href="#about" class="hover:text-brand-700">About</a></li>
                    <li><a href="#cors" class="hover:text-brand-700">CORS network</a></li>
                    <li><a href="#pricing" class="hover:text-brand-700">Pricing</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-slate-900 mb-3">Services</h4>
                <ul class="space-y-2 text-slate-500">
                    <li>Cadastral Surveying</li>
                    <li>Geographic Information Systems</li>
                    <li>Instrument Set-up</li>
                    <li>Hydrographic Surveys</li>
                    <li>Aerial Survey</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-slate-900 mb-3">Contact</h4>
                <p class="text-slate-500 leading-relaxed">
                    2nd Floor, Alphon House,<br>
                    50/52 Toyin Street, Ikeja,<br>
                    Lagos State, Nigeria.
                </p>
                <p class="mt-3 text-slate-600">+234 (0) 808 085 2923</p>
                <p class="text-slate-600">info@sacredioncors.com</p>
            </div>
            <div>
                <h4 class="font-semibold text-slate-900 mb-3">Sacredion</h4>
                <p class="text-slate-500 leading-relaxed">
                    Integrating IT, Engineering, Surveying, Geospatial and Geoscience across construction, telecoms, real estate and oil &amp; gas.
                </p>
            </div>
        </div>

        <div class="border-t border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col sm:flex-row gap-1 justify-between text-xs text-slate-400">
                <p>© {{ date('Y') }} Sacredion Nigeria Limited</p>
                <p>Developed by <a href="https://virtualhorizons.com.ng" target="_blank" rel="noopener noreferrer" class="text-slate-600 hover:text-brand-700">Virtual Horizons</a></p>
            </div>
        </div>
    </footer>

    <a href="#" id="backToTop" class="back-to-top fixed bottom-6 right-6 z-50 w-10 h-10 rounded-full bg-brand-700 text-white flex items-center justify-center shadow-soft" aria-label="Back to top">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </a>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        AOS.init({ duration: 500, easing: 'ease-out', once: true, offset: 24 });

        const header = document.getElementById('site-header');
        const back = document.getElementById('backToTop');
        const onScroll = () => {
            header.classList.toggle('nav-scrolled', window.scrollY > 8);
            back.classList.toggle('show', window.scrollY > 480);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    </script>

    @stack('scripts')
</body>

</html>
