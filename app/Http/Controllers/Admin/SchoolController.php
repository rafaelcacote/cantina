<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $tenantId = $request->get('tenant_id');

        $schools = School::query()
            ->with('tenant')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.schools.index', [
            'title' => 'Escolas',
            'schools' => $schools,
            'search' => $search,
            'tenantId' => $tenantId,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.schools.create', [
            'title' => 'Nova Escola',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'active' => ['required', 'boolean'],
        ]);

        School::query()->create($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'Escola criada com sucesso.');
    }

    public function show(School $school): View
    {
        $school->load('tenant');

        return view('pages.admin.schools.show', [
            'title' => 'Detalhes da Escola',
            'school' => $school,
        ]);
    }

    public function edit(School $school): View
    {
        return view('pages.admin.schools.edit', [
            'title' => 'Editar Escola',
            'school' => $school,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'active' => ['required', 'boolean'],
        ]);

        $school->update($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'Escola atualizada com sucesso.');
    }
}
