<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Welcome back</h2>
        <p class="text-sm text-slate-500 mt-1">Sign in to continue to your GNSS workspace.</p>
    </div>

    <x-auth-session-status class="mb-4 text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" class="text-slate-700" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" class="text-slate-700" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand-700 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <x-recaptcha />

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-slate-600 hover:text-brand-700 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 rounded-lg bg-brand-700 hover:bg-brand-900 focus:bg-brand-900 active:bg-brand-900 focus:ring-brand-500">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <p class="mt-6 text-sm text-slate-600 text-center">
            New here?
            <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:text-brand-900">Create account</a>
        </p>
    </form>
</x-guest-layout>
