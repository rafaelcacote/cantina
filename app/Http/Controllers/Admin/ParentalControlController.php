<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentalControl;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentalControlController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $enabled = $request->string('enabled')->toString();

        $controls = ParentalControl::query()
            ->with('student')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($enabled !== '', fn ($query) => $query->where('enabled', $enabled === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.parental_controls.index', [
            'title' => 'Controles Parentais',
            'controls' => $controls,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'enabled' => $enabled,
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.parental_controls.create', [
            'title' => 'Novo Controle Parental',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $payload = $this->preparePayload($validated, $request);
        $control = ParentalControl::create($payload);

        return redirect()
            ->route('admin.parental-controls.show', $control)
            ->with('success', 'Controle parental criado com sucesso.');
    }

    public function show(ParentalControl $parentalControl): View
    {
        $parentalControl->load(['student', 'allowedCategories.category', 'blockedProducts.product']);

        return view('pages.admin.parental_controls.show', [
            'title' => 'Detalhes do Controle Parental',
            'control' => $parentalControl,
            'tenantName' => DB::table('tenants')->where('id', $parentalControl->tenant_id)->value('name'),
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function edit(ParentalControl $parentalControl): View
    {
        return view('pages.admin.parental_controls.edit', [
            'title' => 'Editar Controle Parental',
            'control' => $parentalControl,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'controlModes' => $this->controlModes(),
        ]);
    }

    public function update(Request $request, ParentalControl $parentalControl): RedirectResponse
    {
        $validated = $request->validate($this->rules($parentalControl));
        $payload = $this->preparePayload($validated, $request);
        $parentalControl->update($payload);

        return redirect()
            ->route('admin.parental-controls.show', $parentalControl)
            ->with('success', 'Controle parental atualizado com sucesso.');
    }

    private function rules(?ParentalControl $control = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
                Rule::unique('parental_controls', 'student_id')
                    ->ignore($control?->id)
                    ->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'control_mode' => ['required', Rule::in(array_keys($this->controlModes()))],
            'daily_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'weekly_spending_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
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
