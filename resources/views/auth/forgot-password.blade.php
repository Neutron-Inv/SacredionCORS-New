<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Reset password</h2>
        <p class="text-sm text-slate-500 mt-1">Enter your email and we'll send you a reset link.</p>
    </div>

    <x-auth-session-status class="mb-4 text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" class="text-slate-700" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-recaptcha />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="rounded-lg bg-brand-700 hover:bg-brand-900 focus:bg-brand-900 active:bg-brand-900 focus:ring-brand-500">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
