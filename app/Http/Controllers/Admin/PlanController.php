<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    private const BILLING_CYCLES = [
        'monthly' => 'Mensal',
        'yearly' => 'Anual',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));

        $plans = Plan::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.plans.index', [
            'title' => 'Planos',
            'plans' => $plans,
            'search' => $search,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.plans.create', [
            'title' => 'Novo Plano',
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);
        $validated['features'] = $this->parseFeatures($request->input('features_text'));
        unset($validated['features_text']);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Plan::query()->create($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plano criado com sucesso.');
    }

    public function show(Plan $plan): View
    {
        $plan->loadCount('subscriptions');

        return view('pages.admin.plans.show', [
            'title' => 'Detalhes do Plano',
            'plan' => $plan,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function edit(Plan $plan): View
    {
        return view('pages.admin.plans.edit', [
            'title' => 'Editar Plano',
            'plan' => $plan,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $this->validatePlan($request, $plan);
        $validated['features'] = $this->parseFeatures($request->input('features_text'));
        unset($validated['features_text']);

        $plan->update($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plano atualizado com sucesso.');
    }

    private function validatePlan(Request $request, ?Plan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('plans', 'slug')->ignore($plan?->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(array_keys(self::BILLING_CYCLES))],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:0'],
            'features_text' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);
    }

    private function parseFeatures(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $features = collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return $features === [] ? null : $features;
    }
}
