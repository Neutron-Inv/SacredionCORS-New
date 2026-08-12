<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Create account</h2>
        <p class="text-sm text-slate-500 mt-1">Join Sacredion CORS with your basic profile details.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" class="text-slate-700" :value="__('First Name')" />
                <x-text-input id="first_name" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" class="text-slate-700" :value="__('Last Name')" />
                <x-text-input id="last_name" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="email" class="text-slate-700" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" class="text-slate-700" :value="__('Phone')" />
            <x-text-input id="phone" class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500" type="text" name="phone" :value="old('phone')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="country" class="text-slate-700" :value="__('Country')" />
            <select id="country" name="country" required
                class="block mt-1 w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm text-slate-800">
                <option value="">Select country</option>
                @foreach ([
                    'Nigeria',
                    'Ghana',
                    'Kenya',
                    'South Africa',
                    'United Kingdom',
                    'United States',
                    'Canada',
                    'Germany',
                    'France',
                    'Netherlands',
                    'United Arab Emirates',
                    'Saudi Arabia',
                    'India',
                    'China',
                    'Australia',
                    'Other',
                ] as $country)
                    <option value="{{ $country }}" @selected(old('country') === $country)>{{ $country }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-slate-600 hover:text-brand-700 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 rounded-lg bg-brand-700 hover:bg-brand-900 focus:bg-brand-900 active:bg-brand-900 focus:ring-brand-500">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
