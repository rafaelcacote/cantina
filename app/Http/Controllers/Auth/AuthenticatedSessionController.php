<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.signin', ['title' => 'Sign In']);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => 'Credenciais inválidas. Verifique e tente novamente.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $defaultRoute = match ($user?->user_type) {
            'super_admin' => route('admin.tenants.index'),
            'tenant_admin' => route('tenant.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($defaultRoute);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin');
    }
}
