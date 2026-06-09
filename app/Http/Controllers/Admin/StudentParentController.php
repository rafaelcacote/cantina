<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentParentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $tenantId = $request->get('tenant_id');

        $links = StudentParent::query()
            ->with(['tenant', 'student', 'parent'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('parent', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.student_parents.index', [
            'title' => 'Vínculos',
            'links' => $links,
            'search' => $search,
            'tenantId' => $tenantId,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.student_parents.create', [
            'title' => 'Novo Vínculo',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'students' => Student::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
            'parents' => ParentGuardian::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudentParent($request);

        StudentParent::query()->create($validated);

        return redirect()
            ->route('admin.student-parents.index')
            ->with('success', 'Vínculo criado com sucesso.');
    }

    public function show(StudentParent $studentParent): View
    {
        $studentParent->load(['tenant', 'student', 'parent']);

        return view('pages.admin.student_parents.show', [
            'title' => 'Detalhes do Vínculo',
            'studentParent' => $studentParent,
        ]);
    }

    public function edit(StudentParent $studentParent): View
    {
        return view('pages.admin.student_parents.edit', [
            'title' => 'Editar Vínculo',
            'studentParent' => $studentParent,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'students' => Student::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
            'parents' => ParentGuardian::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
        ]);
    }

    public function update(Request $request, StudentParent $studentParent): RedirectResponse
    {
        $validated = $this->validateStudentParent($request, $studentParent);

        $studentParent->update($validated);

        return redirect()
            ->route('admin.student-parents.index')
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    private function validateStudentParent(Request $request, ?StudentParent $studentParent = null): array
    {
        $tenantId = (int) $request->input('tenant_id');

        return $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
                Rule::unique('student_parents', 'student_id')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('parent_id', $request->input('parent_id')))
                    ->ignore($studentParent?->id),
            ],
            'parent_id' => [
                'required',
                'integer',
                Rule::exists('parents', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'relationship_type' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['required', 'boolean'],
            'financial_responsible' => ['required', 'boolean'],
        ]);
    }
}
