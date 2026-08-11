<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    private const STATUSES = [
        'trialing' => 'Em trial',
        'active' => 'Ativa',
        'past_due' => 'Em atraso',
        'cancelled' => 'Cancelada',
        'expired' => 'Expirada',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $status = $request->string('status')->toString();
        $tenantId = $request->integer('tenant_id') ?: null;

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('plan', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.subscriptions.index', [
            'title' => 'Assinaturas',
            'subscriptions' => $subscriptions,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
            'search' => $search,
            'status' => $status,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.subscriptions.create', [
            'title' => 'Nova Assinatura',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'plans' => Plan::query()->where('active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubscription($request);
        $subscription = Subscription::query()->create($validated);
        $this->syncTenantDates($subscription);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Assinatura criada com sucesso.');
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['tenant', 'plan']);

        return view('pages.admin.subscriptions.show', [
            'title' => 'Detalhes da Assinatura',
            'subscription' => $subscription,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Subscription $subscription): View
    {
        return view('pages.admin.subscriptions.edit', [
            'title' => 'Editar Assinatura',
            'subscription' => $subscription,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'plans' => Plan::query()->orderBy('name')->get(['id', 'name', 'price']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $this->validateSubscription($request);
        $subscription->update($validated);
        $this->syncTenantDates($subscription->fresh());

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Assinatura atualizada com sucesso.');
    }

    private function validateSubscription(Request $request): array
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'trial_ends_at' => ['nullable', 'date'],
            'next_billing_at' => ['nullable', 'date'],
            'cancelled_at' => ['nullable', 'date'],
        ]);

        if ($validated['status'] === 'cancelled' && empty($validated['cancelled_at'])) {
            $validated['cancelled_at'] = now();
        }

        return $validated;
    }

    private function syncTenantDates(Subscription $subscription): void
    {
        $subscription->tenant?->update([
            'trial_ends_at' => $subscription->trial_ends_at,
            'subscription_ends_at' => $subscription->ends_at,
        ]);
    }
}
