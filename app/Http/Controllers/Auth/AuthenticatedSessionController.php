<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.signin', ['title' => 'Entrar']);
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
            'tenant_admin', 'manager' => route('tenant.dashboard'),
            'operator' => route('operator.dashboard'),
            'parent' => route('parent.dashboard'),
            'student' => route('student.dashboard'),
            'requester' => route('requester.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($defaultRoute);
    }

    public function destroy(Request $request): RedirectResponse|Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return Inertia::location(route('signin'));
        }

        return redirect()->route('signin');
    }
}
