<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesTenantLogo;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    use HandlesTenantLogo;
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
        $validated = $this->validateTenant($request);
        $validated['logo_url'] = $this->storeTenantLogo($request);
        unset($validated['logo']);

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
        $validated = $this->validateTenant($request, $tenant);

        if ($request->hasFile('logo')) {
            $this->deleteStoredTenantLogo($tenant->logo_url);
            $validated['logo_url'] = $this->storeTenantLogo($request);
        }

        unset($validated['logo']);

        $tenant->update($validated);

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant atualizado com sucesso.');
    }

    private function validateTenant(Request $request, ?Tenant $tenant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenant?->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pix' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
        ]);
    }
}
