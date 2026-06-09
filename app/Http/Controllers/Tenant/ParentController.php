<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));

        $parents = ParentGuardian::query()
            ->with('user')
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.tenant.parents.index', [
            'title' => 'Responsáveis',
            'parents' => $parents,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parents.create', [
            'title' => 'Novo Responsável',
            'users' => User::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateParent($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $parent = ParentGuardian::query()->create($validated);

        return redirect()
            ->route('tenant.parents.show', $parent)
            ->with('success', 'Responsável criado com sucesso.');
    }

    public function show(Request $request, ParentGuardian $parent): View
    {
        $this->ensureParentBelongsToTenant($request, $parent);
        $parent->load('user');

        return view('pages.tenant.parents.show', [
            'title' => 'Detalhes do Responsável',
            'parent' => $parent,
        ]);
    }

    public function edit(Request $request, ParentGuardian $parent): View
    {
        $this->ensureParentBelongsToTenant($request, $parent);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parents.edit', [
            'title' => 'Editar Responsável',
            'parent' => $parent,
            'users' => User::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, ParentGuardian $parent): RedirectResponse
    {
        $this->ensureParentBelongsToTenant($request, $parent);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateParent($request, $tenantId);

        $parent->update($validated);

        return redirect()
            ->route('tenant.parents.show', $parent)
            ->with('success', 'Responsável atualizado com sucesso.');
    }

    private function validateParent(Request $request, int $tenantId): array
    {
        return $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }

    private function ensureParentBelongsToTenant(Request $request, ParentGuardian $parent): void
    {
        if ((int) $parent->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
