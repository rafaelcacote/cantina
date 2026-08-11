<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalControlController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $enabled = $request->string('enabled')->toString();

        $controls = ParentalControl::query()
            ->with('student')
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($enabled !== '', fn ($query) => $query->where('enabled', $enabled === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.parental_controls.index', [
            'title' => 'Controles Parentais',
            'controls' => $controls,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'studentId' => $studentId,
            'enabled' => $enabled,
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_controls.create', [
            'title' => 'Novo Controle Parental',
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateControl($request, $tenantId);
        $payload = $this->preparePayload($validated, $request);
        $payload['tenant_id'] = $tenantId;

        $control = ParentalControl::query()->create($payload);

        return redirect()
            ->route('tenant.parental-controls.show', $control)
            ->with('success', 'Controle parental criado com sucesso.');
    }

    public function show(Request $request, ParentalControl $parentalControl): View
    {
        $this->ensureBelongsToTenant($request, $parentalControl);
        $parentalControl->load(['student', 'allowedCategories.category', 'blockedProducts.product']);

        return view('pages.tenant.parental_controls.show', [
            'title' => 'Detalhes do Controle Parental',
            'control' => $parentalControl,
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function edit(Request $request, ParentalControl $parentalControl): View
    {
        $this->ensureBelongsToTenant($request, $parentalControl);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.parental_controls.edit', [
            'title' => 'Editar Controle Parental',
            'control' => $parentalControl,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function update(Request $request, ParentalControl $parentalControl): RedirectResponse
    {
        $this->ensureBelongsToTenant($request, $parentalControl);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateControl($request, $tenantId, $parentalControl);
        $payload = $this->preparePayload($validated, $request);
        $parentalControl->update($payload);

        return redirect()
            ->route('tenant.parental-controls.show', $parentalControl)
            ->with('success', 'Controle parental atualizado com sucesso.');
    }

    private function validateControl(Request $request, int $tenantId, ?ParentalControl $control = null): array
    {
        return $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('parental_controls', 'student_id')
                    ->ignore($control?->id)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'control_mode' => ['required', Rule::in(array_keys($this->controlModes()))],
            'daily_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'weekly_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function preparePayload(array $validated, Request $request): array
    {
        $validated['enabled'] = $request->boolean('enabled');
        $validated['allow_tab_usage'] = $request->boolean('allow_tab_usage');
        $validated['allow_wallet_usage'] = $request->boolean('allow_wallet_usage');
        $validated['allow_convenience_access'] = $request->boolean('allow_convenience_access');
        $validated['allow_snack_access'] = $request->boolean('allow_snack_access');

        return $validated;
    }

    private function ensureBelongsToTenant(Request $request, ParentalControl $control): void
    {
        if ((int) $control->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function controlModes(): array
    {
        return [
            'none' => 'Sem restrição',
            'allowlist' => 'Somente permitidos',
            'blocklist' => 'Lista de bloqueados',
            'mixed' => 'Misto',
        ];
    }
}
