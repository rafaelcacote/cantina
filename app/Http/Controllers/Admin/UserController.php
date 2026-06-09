<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const USER_TYPES = [
        'super_admin',
        'tenant_admin',
        'manager',
        'operator',
        'parent',
        'student',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.users.index', [
            'title' => 'Usuários',
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.users.create', [
            'title' => 'Novo Usuário',
            'userTypes' => self::USER_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'user_type' => ['required', Rule::in(self::USER_TYPES)],
            'password' => ['required', 'string', 'min:6'],
            'active' => ['required', 'boolean'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'active' => (bool) $validated['active'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function show(User $user): View
    {
        return view('pages.admin.users.show', [
            'title' => 'Detalhes do Usuário',
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        return view('pages.admin.users.edit', [
            'title' => 'Editar Usuário',
            'user' => $user,
            'userTypes' => self::USER_TYPES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'user_type' => ['required', Rule::in(self::USER_TYPES)],
            'active' => ['required', 'boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'active' => (bool) $validated['active'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }
}
