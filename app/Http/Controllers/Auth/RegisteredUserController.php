<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartMerger;
use App\Support\PhoneNumber;
use App\Support\SafeRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     */
    public function store(Request $request, CartMerger $cartMerger): RedirectResponse
    {
        $normalizedPhone = $request->filled('phone') ? PhoneNumber::normalize((string) $request->input('phone')) : null;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($normalizedPhone && User::where('phone', $normalizedPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is already registered.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $normalizedPhone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('Customer');

        event(new Registered($user));

        $oldSessionId = $request->session()->getId();

        Auth::login($user);

        $request->session()->regenerate();

        $cartMerger->mergeGuestSessionIntoUser($oldSessionId, $user);

        $redirect = SafeRedirect::resolve($request->input('redirect'));

        return redirect($redirect ?? route('dashboard', absolute: false));
    }
}
