@if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
    <div class="mt-4">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
    </div>
@endif
