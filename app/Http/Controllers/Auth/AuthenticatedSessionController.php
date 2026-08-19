<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartMerger;
use App\Support\SafeRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartMerger $cartMerger): RedirectResponse
    {
        $request->authenticate();

        // Capture before regenerate() rotates the session ID, or the guest
        // cart items (keyed on the old ID) become unreachable.
        $oldSessionId = $request->session()->getId();

        $request->session()->regenerate();

        $cartMerger->mergeGuestSessionIntoUser($oldSessionId, $request->user());

        $redirect = SafeRedirect::resolve($request->input('redirect'));

        return redirect()->intended($redirect ?? route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
