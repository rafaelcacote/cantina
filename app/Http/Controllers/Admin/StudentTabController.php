<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentTab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentTabController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $active = $request->string('active')->toString();

        $tabs = StudentTab::query()
            ->with('student')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($active !== '', fn ($query) => $query->where('active', $active === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.student_tabs.index', [
            'title' => 'Fiados',
            'tabs' => $tabs,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'active' => $active,
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.student_tabs.create', [
            'title' => 'Novo Fiado',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['active'] = $request->boolean('active');

        $tab = StudentTab::create($validated);

        return redirect()
            ->route('admin.student-tabs.show', $tab)
            ->with('success', 'Conta de fiado criada com sucesso.');
    }

    public function show(StudentTab $studentTab): View
    {
        $studentTab->load(['student', 'entries.creator']);

        return view('pages.admin.student_tabs.show', [
            'title' => 'Detalhes do Fiado',
            'tab' => $studentTab,
            'tenantName' => DB::table('tenants')->where('id', $studentTab->tenant_id)->value('name'),
        ]);
    }

    public function edit(StudentTab $studentTab): View
    {
        return view('pages.admin.student_tabs.edit', [
            'title' => 'Editar Fiado',
            'tab' => $studentTab,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'cycleTypes' => $this->cycleTypes(),
        ]);
    }

    public function update(Request $request, StudentTab $studentTab): RedirectResponse
    {
        $validated = $request->validate($this->rules($studentTab));
        $validated['active'] = $request->boolean('active');
        $studentTab->update($validated);

        return redirect()
            ->route('admin.student-tabs.show', $studentTab)
            ->with('success', 'Conta de fiado atualizada com sucesso.');
    }

    private function rules(?StudentTab $tab = null): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
                Rule::unique('student_tabs', 'student_id')
                    ->ignore($tab?->id)
                    ->where(fn ($query) => $query->where('tenant_id', request('tenant_id'))),
            ],
            'current_balance' => ['required', 'numeric', 'min:0'],
            'billing_cycle_type' => ['required', Rule::in(array_keys($this->cycleTypes()))],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'active' => ['nullable', 'boolean'],
        ];
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
