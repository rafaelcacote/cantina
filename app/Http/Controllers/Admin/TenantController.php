<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));

        $tenants = Tenant::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.tenants.index', [
            'title' => 'Tenants',
            'tenants' => $tenants,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.tenants.create', [
            'title' => 'Novo Tenant',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Tenant::query()->create($validated);

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant criado com sucesso.');
    }

    public function show(Tenant $tenant): View
    {
        return view('pages.admin.tenants.show', [
            'title' => 'Detalhes do Tenant',
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('pages.admin.tenants.edit', [
            'title' => 'Editar Tenant',
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenant->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant atualizado com sucesso.');
    }
}
