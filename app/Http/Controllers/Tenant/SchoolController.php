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
            'addressParts' => [
                'street' => '',
                'number' => '',
                'neighborhood' => '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        $validated = $this->validateSchool($request);
        $validated['tenant_id'] = $tenantId;
        $validated['address'] = School::composeAddress(
            $validated['street'] ?? null,
            $validated['number'] ?? null,
            $validated['neighborhood'] ?? null,
        );

        unset($validated['street'], $validated['number'], $validated['neighborhood']);

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
            'addressParts' => $school->addressParts(),
        ]);
    }

    public function edit(Request $request, School $school): View
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        return view('pages.tenant.schools.edit', [
            'title' => 'Editar Escola',
            'school' => $school,
            'addressParts' => $school->addressParts(),
        ]);
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        $validated = $this->validateSchool($request);
        $validated['address'] = School::composeAddress(
            $validated['street'] ?? null,
            $validated['number'] ?? null,
            $validated['neighborhood'] ?? null,
        );

        unset($validated['street'], $validated['number'], $validated['neighborhood']);

        $school->update($validated);

        return redirect()
            ->route('tenant.schools.show', $school)
            ->with('success', 'Escola atualizada com sucesso.');
    }

    public function destroy(Request $request, School $school): RedirectResponse
    {
        $this->ensureSchoolBelongsToTenant($request, $school);

        if ($school->students()->exists() || $school->orders()->exists() || $school->dailyMenus()->exists()) {
            return back()->withErrors([
                'delete' => 'Não é possível excluir a escola enquanto houver alunos, pedidos ou cardápios vinculados.',
            ]);
        }

        $school->delete();

        return redirect()
            ->route('tenant.schools.index')
            ->with('success', 'Escola excluída com sucesso.');
    }

    private function validateSchool(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);
    }

    private function ensureSchoolBelongsToTenant(Request $request, School $school): void
    {
        if ((int) $school->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
