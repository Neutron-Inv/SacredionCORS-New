@extends('layouts.main')

@section('title', 'Privacy Policy — SOGPOS')

@section('content')
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -right-16 h-72 w-72 rounded-full bg-brand-50 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-48 w-48 rounded-full bg-brand-100/40 blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-6 pt-28 pb-14">
            <p class="text-[13px] font-medium text-brand-700 tracking-wide">Legal</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-slate-900">
                Privacy Policy
            </h1>
            <p class="mt-4 max-w-2xl text-slate-600 leading-relaxed">
                How Sacredion Nigeria Limited collects, uses, and protects personal data across the SOGPOS website
                and the SOGPOS Mobile app (Android &amp; iOS).
            </p>
            <div class="mt-6 flex flex-wrap gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
                    Effective: {{ $effectiveDate }}
                </span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
                    Last updated: {{ $lastUpdated }}
                </span>
            </div>
        </div>
    </section>

    <section class="py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-[240px_minmax(0,1fr)] gap-10 items-start">
            <aside class="lg:sticky lg:top-24 hidden lg:block">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">On this page</p>
                <nav class="space-y-1.5 text-sm text-slate-600">
                    @foreach ([
                        'who' => 'Who we are',
                        'scope' => 'Scope',
                        'collect' => 'Data we collect',
                        'mobile' => 'SOGPOS Mobile',
                        'permissions' => 'App permissions',
                        'use' => 'How we use data',
                        'share' => 'Sharing & processors',
                        'retention' => 'Retention',
                        'security' => 'Security',
                        'rights' => 'Your rights',
                        'children' => 'Children',
                        'international' => 'International transfers',
                        'changes' => 'Policy changes',
                        'contact' => 'Contact us',
                    ] as $id => $label)
                        <a href="#{{ $id }}" class="block rounded-lg px-3 py-1.5 hover:bg-brand-50 hover:text-brand-700 transition">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <article class="space-y-10 text-slate-600 leading-relaxed">
                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5 sm:p-6">
                    <p class="text-sm text-slate-700">
                        This policy is written to meet the disclosure requirements of the
                        <strong class="font-medium text-slate-900">Google Play Store</strong> and
                        <strong class="font-medium text-slate-900">Apple App Store</strong>, including data-safety /
                        privacy nutrition labels for location, account, and diagnostic data used by SOGPOS Mobile.
                    </p>
                </div>

                <section id="who" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">1. Who we are</h2>
                    <div class="mt-4 space-y-3">
                        <p>
                            SOGPOS (Sacredion Online GNSS Positioning Service) is operated by
                            <strong class="font-medium text-slate-900">Sacredion Nigeria Limited</strong>
                            (“Sacredion”, “we”, “us”, or “our”), in collaboration with Tersus GNSS Inc. for GNSS
                            technology support.
                        </p>
                        <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm">
                            <p class="font-medium text-slate-900">Data controller</p>
                            <p class="mt-2">Sacredion Nigeria Limited</p>
                            <p>2nd Floor, Alphon House, 50/52 Toyin Street, Ikeja, Lagos State, Nigeria</p>
                            <p class="mt-2">
                                Email:
                                <a href="mailto:info@sacredioncors.com" class="text-brand-700 hover:underline">info@sacredioncors.com</a>
                                ·
                                <a href="mailto:admin@sacredioncors.com" class="text-brand-700 hover:underline">admin@sacredioncors.com</a>
                            </p>
                            <p>Phone: +234 (0) 808 085 2923</p>
                        </div>
                    </div>
                </section>

                <section id="scope" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">2. Scope</h2>
                    <p class="mt-4">
                        This Privacy Policy applies to:
                    </p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>The SOGPOS website at sacredioncors.com (and related subdomains)</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>The SOGPOS Mobile application for Android and iOS</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>NTRIP / RTK correction services and CORS observation-data access delivered through SOGPOS</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Customer support and account administration related to those services</span></li>
                    </ul>
                    <p class="mt-4 text-sm">
                        By creating an account, using the website, or installing SOGPOS Mobile, you agree to the practices
                        described here. If you do not agree, please do not use the services.
                    </p>
                </section>

                <section id="collect" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">3. Data we collect</h2>
                    <p class="mt-4">
                        We collect only what is needed to provide high-precision GNSS services, secure your account, and
                        improve reliability.
                    </p>

                    <div class="mt-6 grid sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <h3 class="font-medium text-slate-900">Account &amp; identity</h3>
                            <ul class="mt-3 space-y-1.5 text-sm">
                                <li>Full name</li>
                                <li>Email address</li>
                                <li>Phone number</li>
                                <li>Country / location of practice</li>
                                <li>Password (stored hashed; never in plain text)</li>
                                <li>Organisation or company name (if you provide it)</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <h3 class="font-medium text-slate-900">Service &amp; usage</h3>
                            <ul class="mt-3 space-y-1.5 text-sm">
                                <li>Login and session activity</li>
                                <li>Subscription / trial status</li>
                                <li>CORS station and file download history</li>
                                <li>NTRIP connection events (mountpoint, time, success/failure)</li>
                                <li>Support messages you send us</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <h3 class="font-medium text-slate-900">Device &amp; technical</h3>
                            <ul class="mt-3 space-y-1.5 text-sm">
                                <li>Device model, OS version, app version</li>
                                <li>IP address and approximate network region</li>
                                <li>Crash logs and performance diagnostics</li>
                                <li>Browser type (website only)</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-5">
                            <h3 class="font-medium text-slate-900">Location (Mobile)</h3>
                            <ul class="mt-3 space-y-1.5 text-sm">
                                <li>Precise device location / GNSS position while the app is in use</li>
                                <li>Used to deliver RTK corrections and show nearby CORS coverage</li>
                                <li>Not sold or used for advertising</li>
                            </ul>
                        </div>
                    </div>

                    <p class="mt-5 text-sm">
                        We do <strong class="font-medium text-slate-900">not</strong> intentionally collect special-category
                        data (such as biometric templates for identity, health, or political opinions). Payment processing,
                        if offered through third-party gateways, is handled by those providers under their own policies;
                        we receive confirmation of payment status, not full card numbers.
                    </p>
                </section>

                <section id="mobile" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">4. SOGPOS Mobile — how we serve you</h2>
                    <p class="mt-4">
                        SOGPOS Mobile gives surveyors and engineers access to live RTK correction streams (NTRIP) across
                        the Sacredion CORS network and related tooling in the field. To do that safely and correctly, the
                        app processes:
                    </p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Sign-in credentials</strong> so only authorised users can reach the caster and account features.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Precise location / GNSS data</strong> so correction streams can be selected and applied for centimetre-level positioning.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Network connectivity data</strong> to maintain the NTRIP session and report connection health.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Diagnostic events</strong> (crashes, failed connections) so we can keep the service reliable.</span></li>
                    </ul>
                    <div class="mt-5 rounded-xl border border-slate-200 bg-white p-5 text-sm">
                        <p class="font-medium text-slate-900">Store disclosure summary</p>
                        <p class="mt-2">
                            For Google Play Data safety and Apple App Privacy labels, SOGPOS Mobile may declare collection of:
                            <em>Name, Email, Phone, Precise Location, Device IDs / diagnostics, App activity (NTRIP usage)</em>.
                            Data is used for <em>App functionality, Account management, Analytics (service reliability), and Customer support</em>.
                            Data is <em>not</em> used for personalised advertising and is <em>not</em> sold.
                        </p>
                    </div>
                </section>

                <section id="permissions" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">5. App permissions</h2>
                    <p class="mt-4 text-sm">
                        Depending on your device and OS version, SOGPOS Mobile may request:
                    </p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="text-left font-medium p-3">Permission</th>
                                    <th class="text-left font-medium p-3">Why we need it</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="p-3 font-medium text-slate-900">Location (precise / while in use)</td>
                                    <td class="p-3">Deliver RTK corrections and show CORS coverage relative to your survey position.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium text-slate-900">Internet / network</td>
                                    <td class="p-3">Authenticate, fetch mountpoint lists, and stream NTRIP corrections.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium text-slate-900">Notifications (optional)</td>
                                    <td class="p-3">Service alerts such as connection status or account notices.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-sm">
                        You can revoke permissions in your device settings. Some features (especially live RTK) will not
                        work without location and network access.
                    </p>
                </section>

                <section id="use" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">6. How we use data</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Create and secure your SOGPOS account and verify email when required</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Provide NTRIP/RTK corrections and CORS RINEX downloads</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Authenticate sessions and prevent abuse or unauthorised access</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Operate billing, trials, and subscription entitlements</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Respond to support requests and service notices</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Monitor uptime, diagnose faults, and improve the network and apps</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Comply with legal obligations and enforce our terms of use</span></li>
                    </ul>
                    <p class="mt-4 text-sm">
                        Legal bases (where applicable under privacy laws) include: performance of a contract (providing the
                        service you requested), legitimate interests (security and service improvement), and consent (optional
                        permissions such as notifications or marketing emails).
                    </p>
                </section>

                <section id="share" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">7. Sharing &amp; processors</h2>
                    <p class="mt-4">
                        We do <strong class="font-medium text-slate-900">not sell</strong> personal data. We share data only
                        as needed to run SOGPOS:
                    </p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Infrastructure &amp; hosting</strong> — servers, email delivery, and storage providers that process data on our instructions.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">GNSS technology partners</strong> — including Tersus GNSS Inc. where required for platform or receiver interoperability.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">App stores</strong> — Google and Apple receive standard distribution / billing metadata under their policies.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Legal &amp; safety</strong> — when required by law, court order, or to protect users and the network.</span></li>
                    </ul>
                    <p class="mt-4 text-sm">
                        Third parties acting for us are expected to protect data and use it only for the contracted purpose.
                    </p>
                </section>

                <section id="retention" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">8. Data retention</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Account data</strong> — kept while your account is active and for a reasonable period afterward for support, billing, and legal records.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">NTRIP / usage logs</strong> — retained for operational monitoring and abuse prevention, then deleted or anonymised.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Crash / diagnostic data</strong> — kept only as long as useful for troubleshooting.</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span><strong class="font-medium text-slate-900">Location processed in-session</strong> — used to serve corrections in real time; we do not build advertising profiles from your survey positions.</span></li>
                    </ul>
                </section>

                <section id="security" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">9. Security</h2>
                    <p class="mt-4">
                        We use industry-standard safeguards appropriate to the sensitivity of GNSS service accounts,
                        including encrypted transport (HTTPS/TLS) for the website and app API traffic, hashed passwords,
                        access controls on servers, and operational monitoring. No method of transmission or storage is
                        100% secure; please choose a strong password and keep your credentials private.
                    </p>
                </section>

                <section id="rights" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">10. Your rights</h2>
                    <p class="mt-4">
                        Subject to applicable law, you may request to:
                    </p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Access the personal data we hold about you</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Correct inaccurate information</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Delete your account and associated personal data (where we are not required to retain it)</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Withdraw optional consents (e.g. marketing or non-essential permissions)</span></li>
                        <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-brand-700 shrink-0"></span><span>Export a copy of your account information</span></li>
                    </ul>
                    <p class="mt-4 text-sm">
                        To exercise these rights, email
                        <a href="mailto:info@sacredioncors.com" class="text-brand-700 hover:underline">info@sacredioncors.com</a>
                        with the subject line “Privacy request”. We may need to verify your identity before acting.
                    </p>
                </section>

                <section id="children" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">11. Children</h2>
                    <p class="mt-4">
                        SOGPOS is a professional GNSS service intended for adults and organisational users. We do not
                        knowingly collect personal data from children under 13 (or the minimum age required in your
                        jurisdiction). If you believe a child has provided data to us, contact us and we will delete it.
                    </p>
                </section>

                <section id="international" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">12. International transfers</h2>
                    <p class="mt-4">
                        Our primary operations are in Nigeria. Some processors (email, hosting, app store, or GNSS
                        partners) may process data in other countries. Where that happens, we take steps appropriate to
                        the risk so that your data remains protected.
                    </p>
                </section>

                <section id="changes" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">13. Changes to this policy</h2>
                    <p class="mt-4">
                        We may update this Privacy Policy when our services, laws, or store requirements change. The
                        “Last updated” date at the top will be revised. Material changes may also be communicated by email
                        or in-app notice. Continued use of SOGPOS after an update means you accept the revised policy.
                    </p>
                </section>

                <section id="contact" class="scroll-mt-24">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">14. Contact us</h2>
                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6">
                        <p class="text-sm">
                            Questions about privacy, data deletion, or store disclosures:
                        </p>
                        <p class="mt-4 font-medium text-slate-900">Sacredion Nigeria Limited — SOGPOS</p>
                        <p class="mt-1 text-sm">2nd Floor, Alphon House, 50/52 Toyin Street, Ikeja, Lagos, Nigeria</p>
                        <p class="mt-3 text-sm">
                            <a href="mailto:info@sacredioncors.com" class="text-brand-700 hover:underline">info@sacredioncors.com</a><br>
                            <a href="mailto:admin@sacredioncors.com" class="text-brand-700 hover:underline">admin@sacredioncors.com</a><br>
                            +234 (0) 808 085 2923
                        </p>
                        <a href="{{ route('home') }}#contact"
                            class="inline-flex mt-5 px-4 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition">
                            Back to SOGPOS
                        </a>
                    </div>
                </section>
            </article>
        </div>
    </section>
@endsection
