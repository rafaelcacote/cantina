<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentTab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentTabController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $active = $request->string('active')->toString();

        $tabs = StudentTab::query()
            ->with('student')
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($active !== '', fn ($query) => $query->where('active', $active === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.student_tabs.index', [
            'title' => 'Fiados',
            'tabs' => $tabs,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'studentId' => $studentId,
            'active' => $active,
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.student_tabs.create', [
            'title' => 'Novo Fiado',
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateTab($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $tab = StudentTab::query()->create($validated);

        return redirect()
            ->route('tenant.student-tabs.show', $tab)
            ->with('success', 'Conta de fiado criada com sucesso.');
    }

    public function show(Request $request, StudentTab $studentTab): View
    {
        $this->ensureTabBelongsToTenant($request, $studentTab);
        $studentTab->load(['student', 'entries.creator']);

        return view('pages.tenant.student_tabs.show', [
            'title' => 'Detalhes do Fiado',
            'tab' => $studentTab,
            'cycleTypes' => $this->cycleTypes(),
            'entryStatuses' => [
                'open' => 'Aberto',
                'paid' => 'Pago',
                'cancelled' => 'Cancelado',
            ],
        ]);
    }

    public function edit(Request $request, StudentTab $studentTab): View
    {
        $this->ensureTabBelongsToTenant($request, $studentTab);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.student_tabs.edit', [
            'title' => 'Editar Fiado',
            'tab' => $studentTab,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function update(Request $request, StudentTab $studentTab): RedirectResponse
    {
        $this->ensureTabBelongsToTenant($request, $studentTab);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateTab($request, $tenantId, $studentTab);
        $studentTab->update($validated);

        return redirect()
            ->route('tenant.student-tabs.show', $studentTab)
            ->with('success', 'Conta de fiado atualizada com sucesso.');
    }

    public function destroy(Request $request, StudentTab $studentTab): RedirectResponse
    {
        $this->ensureTabBelongsToTenant($request, $studentTab);
        $studentTab->delete();

        return redirect()
            ->route('tenant.student-tabs.index')
            ->with('success', 'Conta de fiado excluída com sucesso.');
    }

    private function validateTab(Request $request, int $tenantId, ?StudentTab $tab = null): array
    {
        return $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('student_tabs', 'student_id')
                    ->ignore($tab?->id)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'current_balance' => ['required', 'numeric', 'min:0'],
            'billing_cycle_type' => ['required', Rule::in(array_keys($this->cycleTypes()))],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'active' => ['required', 'boolean'],
        ]);
    }

    private function ensureTabBelongsToTenant(Request $request, StudentTab $tab): void
    {
        if ((int) $tab->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function cycleTypes(): array
    {
        return [
            'monthly' => 'Mensal',
            'weekly' => 'Semanal',
            'daily' => 'Diário',
        ];
    }
}
