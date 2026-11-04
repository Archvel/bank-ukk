<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User|null $user */
            $user = Auth::user();

            if ($user?->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user?->isTeller()) {
                return redirect()->route('teller.counter');
            }

            if ($user?->isNasabah()) {
                return redirect()->route('nasabah.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()?->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($request->user()?->isTeller()) {
            return redirect()->intended(route('teller.counter', absolute: false));
        }

        if ($request->user()?->isNasabah()) {
            return redirect()->intended(route('nasabah.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
