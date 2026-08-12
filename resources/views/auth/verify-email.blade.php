<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Verify your email</h2>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Thanks for signing up. Before you can download GNSS files, please verify your email address by clicking the link we sent you.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="rounded-lg bg-brand-700 hover:bg-brand-900 focus:bg-brand-900 active:bg-brand-900 focus:ring-brand-500">
                Resend verification email
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-600 hover:text-brand-700 underline">
                Log out
            </button>
        </form>
    </div>
</x-guest-layout>
