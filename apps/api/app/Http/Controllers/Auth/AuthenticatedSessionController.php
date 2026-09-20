<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if ($request->user()->isStaff() && $request->user()->staff_suspended_at !== null) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'This staff account has been suspended. Contact a Helmio administrator.',
            ]);
        }

        $request->session()->regenerate();

        $request->user()->forceFill([
            'last_login_at' => now(),
        ])->save();

        if ($request->user()->isStaff()) {
            $route = match (true) {
                $request->user()->hasStaffPermission('dashboard.view') => 'admin.dashboard',
                $request->user()->hasStaffPermission('support.view') => 'admin.support.index',
                $request->user()->hasStaffPermission('customers.view') => 'admin.customers.index',
                default => 'profile.edit',
            };

            return redirect()->route($route);
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

        return redirect('/');
    }
}
