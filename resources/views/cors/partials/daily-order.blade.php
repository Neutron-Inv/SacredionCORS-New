<div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm" x-data="dailyOrder(@js($dailyOrder))">
    <h3 class="font-semibold text-slate-900">24-hour data</h3>
    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
        Order one merged file for a completed day at this station. Today is excluded while files are still being recorded.
        The download stays available for {{ $dailyOrder['downloadDays'] }} days.
    </p>

    @if (count($dailyOrder['dates']) === 0)
        <p class="mt-4 text-sm text-slate-600">No completed day is available for {{ $station->name }} yet.</p>
    @else
        <p class="mt-4 text-xs text-slate-400">
            Earliest day: {{ $dailyOrder['dates'][0]['label'] }}. Days without files cannot be selected.
        </p>

        <div class="mt-3 rounded-xl border border-slate-200 p-3">
            <div class="flex items-center justify-between">
                <button type="button" @click="previousMonth()" :disabled="!canGoPrevious" aria-label="Previous month"
                    class="h-8 w-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent">‹</button>
                <p class="text-sm font-medium text-slate-900" x-text="monthLabel"></p>
                <button type="button" @click="nextMonth()" :disabled="!canGoNext" aria-label="Next month"
                    class="h-8 w-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent">›</button>
            </div>
            <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-slate-400">
                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
            </div>
            <div class="mt-1 grid grid-cols-7 gap-1">
                <template x-for="cell in calendarCells" :key="cell.key">
                    <div class="h-8">
                        <button type="button"
                            x-show="cell.day"
                            class="h-8 w-full rounded-lg text-sm"
                            :class="cellClass(cell)"
                            :disabled="!cell.enabled"
                            :aria-pressed="cell.value === date"
                            :aria-label="cell.value || ''"
                            @click="selectDate(cell.value)"
                            x-text="cell.day"></button>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-3 text-sm text-slate-600" x-cloak>
            <template x-if="loading">
                <p>Checking files…</p>
            </template>
            <template x-if="!loading && count !== null">
                <p>
                    <span class="font-semibold text-slate-900" x-text="count"></span>
                    files found for this day.
                    {{-- <span class="block text-xs text-slate-400 mt-1" x-show="extensionLabel" x-text="extensionLabel"></span> --}}
                </p>
            </template>
            <p class="text-red-600" x-show="error" x-text="error"></p>
        </div>

        @if (! $dailyOrder['canOrder'])
            <p class="mt-4 text-sm text-slate-600">
                @auth
                    <a href="{{ route('verification.notice') }}" class="font-medium text-brand-700 underline">Verify your email</a>
                    before ordering this file.
                @else
                    <a href="{{ route('login') }}" class="font-medium text-brand-700 underline">Sign in</a>
                    or
                    <a href="{{ route('register') }}" class="font-medium text-brand-700 underline">register</a>
                    to order this file.
                @endauth
            </p>
        @elseif ($dailyOrder['mode'] === 'unavailable')
            <p class="mt-4 text-sm text-amber-800">24-hour ordering is not available right now.</p>
        @else
            <button type="button" @click="openModal()" :disabled="loading || !count"
                class="mt-4 w-full px-4 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Order this day · {{ $dailyOrder['priceLabel'] }}
            </button>
        @endif
    @endif

    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="daily-order-title">
        <div class="absolute inset-0 bg-slate-900/50" @click="modal = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @keydown.escape.window="modal = false">
            <h3 id="daily-order-title" class="text-lg font-semibold text-slate-900">Pay for 24-hour data</h3>
            <p class="mt-2 text-sm text-slate-500">{{ $station->name }}</p>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Date</dt>
                    <dd class="font-medium text-slate-900" x-text="selectedLabel"></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Files found</dt>
                    <dd class="font-medium text-slate-900" x-text="count ?? '—'"></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Price</dt>
                    <dd class="font-semibold text-slate-900">{{ $dailyOrder['priceLabel'] }}</dd>
                </div>
            </dl>

            <p class="mt-3 text-xs text-slate-400" x-show="extensionLabel" x-text="extensionLabel"></p>

            @if ($dailyOrder['mode'] === 'local')
                <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Local environment: this confirms a test payment and does not charge a card.
                </p>
            @endif

            <p class="mt-4 text-sm text-slate-600" x-show="message" x-text="message"></p>
            <p class="mt-2 text-sm text-red-600" x-show="error" x-text="error"></p>
            <a x-show="downloadUrl" :href="downloadUrl" class="mt-3 inline-flex text-sm font-medium text-brand-700 underline">Download file</a>

            <div class="mt-5 flex gap-3">
                <button type="button" @click="modal = false" class="flex-1 px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Close
                </button>
                <button type="button" @click="pay()" :disabled="paying" x-show="!downloadUrl"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-brand-700 text-white text-sm font-medium hover:bg-brand-900 disabled:opacity-50">
                    <span x-show="!paying">Pay {{ $dailyOrder['priceLabel'] }}</span>
                    <span x-show="paying" x-cloak>Please wait…</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function dailyOrder(config) {
            return {
                dates: config.dates,
                available: Object.fromEntries(config.dates.map((option) => [option.value, true])),
                date: config.dates.length ? config.dates[config.dates.length - 1].value : '',
                visibleYear: null,
                visibleMonth: null,
                count: null,
                extensionLabel: '',
                loading: false,
                paying: false,
                modal: false,
                error: '',
                message: '',
                downloadUrl: null,
                init() {
                    if (!this.date) {
                        return;
                    }
                    const [year, month] = this.date.split('-').map(Number);
                    this.visibleYear = year;
                    this.visibleMonth = month - 1;
                    this.fetchCount();
                },
                get monthLabel() {
                    if (this.visibleYear === null) {
                        return '';
                    }
                    return new Date(this.visibleYear, this.visibleMonth, 1).toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric',
                    });
                },
                get canGoPrevious() {
                    return this.monthOffset(this.dates[0]?.value) < this.monthIndex();
                },
                get canGoNext() {
                    const last = this.dates[this.dates.length - 1]?.value;
                    return this.monthOffset(last) > this.monthIndex();
                },
                get calendarCells() {
                    if (this.visibleYear === null) {
                        return [];
                    }
                    const firstWeekday = new Date(this.visibleYear, this.visibleMonth, 1).getDay();
                    const days = new Date(this.visibleYear, this.visibleMonth + 1, 0).getDate();
                    const cells = [];
                    for (let index = 0; index < firstWeekday; index++) {
                        cells.push({ key: `blank-${index}`, day: null, value: null, enabled: false });
                    }
                    for (let day = 1; day <= days; day++) {
                        const value = `${this.visibleYear}-${String(this.visibleMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        cells.push({
                            key: value,
                            day,
                            value,
                            enabled: Boolean(this.available[value]),
                        });
                    }
                    return cells;
                },
                monthIndex() {
                    return this.visibleYear * 12 + this.visibleMonth;
                },
                monthOffset(value) {
                    if (!value) {
                        return this.monthIndex();
                    }
                    const [year, month] = value.split('-').map(Number);
                    return year * 12 + (month - 1);
                },
                previousMonth() {
                    if (!this.canGoPrevious) {
                        return;
                    }
                    if (this.visibleMonth === 0) {
                        this.visibleMonth = 11;
                        this.visibleYear -= 1;
                        return;
                    }
                    this.visibleMonth -= 1;
                },
                nextMonth() {
                    if (!this.canGoNext) {
                        return;
                    }
                    if (this.visibleMonth === 11) {
                        this.visibleMonth = 0;
                        this.visibleYear += 1;
                        return;
                    }
                    this.visibleMonth += 1;
                },
                selectDate(value) {
                    if (!value || !this.available[value]) {
                        return;
                    }
                    this.date = value;
                    this.fetchCount();
                },
                cellClass(cell) {
                    if (!cell.day || !cell.enabled) {
                        return 'text-slate-300 cursor-not-allowed';
                    }
                    if (cell.value === this.date) {
                        return 'bg-brand-700 text-white font-medium';
                    }
                    return 'text-slate-800 hover:bg-brand-50';
                },
                get selectedLabel() {
                    const match = this.dates.find((option) => option.value === this.date);
                    return match ? match.label : this.date;
                },
                async fetchCount() {
                    this.error = '';
                    this.count = null;
                    this.extensionLabel = '';
                    if (!this.date) {
                        return;
                    }
                    this.loading = true;
                    try {
                        const response = await fetch(`${config.previewUrl}?date=${encodeURIComponent(this.date)}`, {
                            headers: { Accept: 'application/json' },
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            this.error = data.errors?.date?.[0] || data.message || 'Could not check that day.';
                            return;
                        }
                        this.count = data.count;
                        this.extensionLabel = Object.entries(data.extensions || {})
                            .map(([ext, total]) => `${total} × .${ext}`)
                            .join(', ');
                    } catch (error) {
                        this.error = 'Could not check that day.';
                    } finally {
                        this.loading = false;
                    }
                },
                openModal() {
                    if (!this.count) {
                        return;
                    }
                    this.modal = true;
                    this.message = '';
                    this.downloadUrl = null;
                    this.error = '';
                },
                async pay() {
                    this.paying = true;
                    this.error = '';
                    this.message = '';
                    try {
                        const response = await fetch(config.storeUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ date: this.date }),
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            this.error = data.errors?.date?.[0] || data.errors?.payment?.[0] || data.message || 'Could not start payment.';
                            return;
                        }
                        if (data.mode === 'ready' || data.mode === 'processing') {
                            this.message = data.message;
                            this.downloadUrl = data.download_url || null;
                            return;
                        }
                        if (data.mode === 'local') {
                            await this.confirmPayment(data.confirm_url);
                            return;
                        }
                        await this.openPaystack(data);
                    } catch (error) {
                        this.error = 'Could not start payment.';
                    } finally {
                        this.paying = false;
                    }
                },
                async openPaystack(data) {
                    if (!data.access_code) {
                        this.error = 'Payment could not be opened. Try again.';
                        return;
                    }
                    await this.loadPaystack();
                    const popup = new PaystackPop();
                    popup.resumeTransaction(data.access_code, {
                        onSuccess: () => this.confirmPayment(data.confirm_url),
                        onCancel: () => {
                            this.error = 'Payment was cancelled.';
                        },
                        onError: () => {
                            this.error = 'Payment could not be opened. Try again.';
                        },
                    });
                },
                loadPaystack() {
                    return new Promise((resolve, reject) => {
                        if (window.PaystackPop) {
                            resolve();
                            return;
                        }
                        const script = document.createElement('script');
                        script.src = 'https://js.paystack.co/v2/inline.js';
                        script.onload = () => resolve();
                        script.onerror = () => reject(new Error('Paystack failed to load'));
                        document.head.appendChild(script);
                    });
                },
                async confirmPayment(url) {
                    this.paying = true;
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: '{}',
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            this.error = data.message || 'Payment could not be confirmed.';
                            return;
                        }
                        this.message = data.message;
                        this.downloadUrl = data.download_url || null;
                    } finally {
                        this.paying = false;
                    }
                },
            };
        }
    </script>
@endpush
