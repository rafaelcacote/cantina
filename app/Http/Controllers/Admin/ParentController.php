<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $tenantId = $request->get('tenant_id');

        $parents = ParentGuardian::query()
            ->with(['tenant', 'user'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.parents.index', [
            'title' => 'Responsáveis',
            'parents' => $parents,
            'search' => $search,
            'tenantId' => $tenantId,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.parents.create', [
            'title' => 'Novo Responsável',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        ParentGuardian::query()->create($validated);

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'Responsável criado com sucesso.');
    }

    public function show(ParentGuardian $parent): View
    {
        $parent->load(['tenant', 'user']);

        return view('pages.admin.parents.show', [
            'title' => 'Detalhes do Responsável',
            'parent' => $parent,
        ]);
    }

    public function edit(ParentGuardian $parent): View
    {
        return view('pages.admin.parents.edit', [
            'title' => 'Editar Responsável',
            'parent' => $parent,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, ParentGuardian $parent): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $parent->update($validated);

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'Responsável atualizado com sucesso.');
    }
}
