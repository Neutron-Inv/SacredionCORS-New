<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $countries = [
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
        ];

        $request->merge([
            'email' => Str::lower(trim($request->input('email', ''))),
        ]);

        $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:32'],
            'country' => ['required', 'string', 'in:'.implode(',', $countries)],
            'g-recaptcha-response' => [new Recaptcha],
        ]);

        $user = User::create([
            'name' => trim($request->first_name.' '.$request->last_name),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            // Registration is profile-first; password can be set later via forgot-password.
            'password' => Hash::make(Str::password(24)),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
