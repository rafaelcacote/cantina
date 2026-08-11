<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperatorController extends Controller
{
    private const ROLES = [
        'operator' => 'Operador',
        'cashier' => 'Caixa',
        'manager' => 'Gerente',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $tenantId = $request->integer('tenant_id') ?: null;

        $operators = Operator::query()
            ->with(['tenant', 'school', 'user'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('school', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.operators.index', [
            'title' => 'Operadores',
            'operators' => $operators,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'roles' => self::ROLES,
            'search' => $search,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;

        return view('pages.admin.operators.create', [
            'title' => 'Novo Operador',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'schools' => School::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(['id', 'name', 'tenant_id']),
            'users' => User::query()
                ->whereIn('user_type', ['operator', 'manager'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'tenant_id']),
            'roles' => self::ROLES,
            'tenantId' => $tenantId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateOperator($request);
        Operator::query()->create($validated);

        return redirect()
            ->route('admin.operators.index')
            ->with('success', 'Operador vinculado com sucesso.');
    }

    public function show(Operator $operator): View
    {
        $operator->load(['tenant', 'school', 'user']);

        return view('pages.admin.operators.show', [
            'title' => 'Detalhes do Operador',
            'operator' => $operator,
            'roles' => self::ROLES,
        ]);
    }

    public function edit(Operator $operator): View
    {
        return view('pages.admin.operators.edit', [
            'title' => 'Editar Operador',
            'operator' => $operator,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'schools' => School::query()
                ->where('tenant_id', $operator->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name', 'tenant_id']),
            'users' => User::query()
                ->whereIn('user_type', ['operator', 'manager'])
                ->where('tenant_id', $operator->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'tenant_id']),
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, Operator $operator): RedirectResponse
    {
        $validated = $this->validateOperator($request, $operator);
        $operator->update($validated);

        return redirect()
            ->route('admin.operators.index')
            ->with('success', 'Operador atualizado com sucesso.');
    }

    private function validateOperator(Request $request, ?Operator $operator = null): array
    {
        $tenantId = (int) $request->input('tenant_id');

        return $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'school_id' => [
                'nullable',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
                Rule::unique('operators', 'user_id')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId))
                    ->ignore($operator?->id),
            ],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
        ]);
    }
}
