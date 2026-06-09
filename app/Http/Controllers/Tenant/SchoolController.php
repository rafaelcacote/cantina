<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));

        $schools = School::query()
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.tenant.schools.index', [
            'title' => 'Escolas',
            'schools' => $schools,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('pages.tenant.schools.create', [
            'title' => 'Nova Escola',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'active' => ['required', 'boolean'],
        ]);

        $validated['tenant_id'] = $tenantId;

        $school = School::query()->create($validated);

        return redirect()
            ->route('tenant.schools.show', $school)
            ->with('success', 'Escola criada com sucesso.');
    }

    public function show(Request $request, School $school): View
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        return view('pages.tenant.schools.show', [
            'title' => 'Detalhes da Escola',
            'school' => $school,
        ]);
    }

    public function edit(Request $request, School $school): View
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        return view('pages.tenant.schools.edit', [
            'title' => 'Editar Escola',
            'school' => $school,
        ]);
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'active' => ['required', 'boolean'],
        ]);

        $school->update($validated);

        return redirect()
            ->route('tenant.schools.show', $school)
            ->with('success', 'Escola atualizada com sucesso.');
    }

    private function ensureSchoolBelongsToTenant(Request $request, School $school): void
    {
        if ((int) $school->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
