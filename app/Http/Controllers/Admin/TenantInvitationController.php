<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantInvitationController extends Controller
{
    public const TYPES = [
        'tenant_admin' => 'Admin do tenant',
        'parent_registration' => 'Cadastro de responsável',
        'operator' => 'Operador',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $type = $request->string('type')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;

        $invitations = TenantInvitation::query()
            ->with(['tenant', 'creator'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('token', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.tenant-invitations.index', [
            'title' => 'Convites',
            'invitations' => $invitations,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'types' => self::TYPES,
            'search' => $search,
            'type' => $type,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.tenant-invitations.create', [
            'title' => 'Novo Convite',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'active' => ['required', 'boolean'],
        ]);

        $invitation = TenantInvitation::query()->create([
            ...$validated,
            'token' => Str::random(48),
            'used_count' => 0,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.tenant-invitations.show', $invitation)
            ->with('success', 'Convite criado com sucesso.');
    }

    public function show(TenantInvitation $tenantInvitation): View
    {
        $tenantInvitation->load(['tenant', 'creator']);

        return view('pages.admin.tenant-invitations.show', [
            'title' => 'Detalhes do Convite',
            'invitation' => $tenantInvitation,
            'types' => self::TYPES,
            'acceptUrl' => route('invitations.accept', $tenantInvitation->token),
        ]);
    }

    public function edit(TenantInvitation $tenantInvitation): View
    {
        return view('pages.admin.tenant-invitations.edit', [
            'title' => 'Editar Convite',
            'invitation' => $tenantInvitation,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'types' => self::TYPES,
        ]);
    }

    public function update(Request $request, TenantInvitation $tenantInvitation): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'active' => ['required', 'boolean'],
        ]);

        $tenantInvitation->update($validated);

        return redirect()
            ->route('admin.tenant-invitations.show', $tenantInvitation)
            ->with('success', 'Convite atualizado com sucesso.');
    }
}
