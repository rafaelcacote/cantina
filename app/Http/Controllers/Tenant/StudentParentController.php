<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\StudentParent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentParentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));

        $links = StudentParent::query()
            ->with(['student', 'parent'])
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('parent', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.student_parents.index', [
            'title' => 'Vínculos',
            'links' => $links,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $parentId = $request->integer('parent_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;

        if ($parentId) {
            $belongsToTenant = ParentGuardian::query()
                ->where('tenant_id', $tenantId)
                ->where('id', $parentId)
                ->exists();

            if (! $belongsToTenant) {
                abort(404);
            }
        }

        if ($studentId) {
            $belongsToTenant = Student::query()
                ->where('tenant_id', $tenantId)
                ->where('id', $studentId)
                ->exists();

            if (! $belongsToTenant) {
                abort(404);
            }
        }

        return view('pages.tenant.student_parents.create', [
            'title' => 'Novo Vínculo',
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'parents' => ParentGuardian::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'selectedParentId' => $parentId,
            'selectedStudentId' => $studentId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudentParent($request, $tenantId);
        $validated['tenant_id'] = $tenantId;

        $link = StudentParent::query()->create($validated);

        if ($request->input('_context') === 'student' && ! empty($validated['student_id'])) {
            return redirect()
                ->route('tenant.students.index')
                ->with('success', 'Responsável vinculado com sucesso.');
        }

        if ($request->input('_context') === 'parent' && ! empty($validated['parent_id'])) {
            return redirect()
                ->route('tenant.parents.students', $validated['parent_id'])
                ->with('success', 'Aluno vinculado com sucesso.');
        }

        return redirect()
            ->route('tenant.student-parents.show', $link)
            ->with('success', 'Vínculo criado com sucesso.');
    }

    public function show(Request $request, StudentParent $studentParent): View
    {
        $this->ensureLinkBelongsToTenant($request, $studentParent);
        $studentParent->load(['student', 'parent']);

        return view('pages.tenant.student_parents.show', [
            'title' => 'Detalhes do Vínculo',
            'studentParent' => $studentParent,
        ]);
    }

    public function edit(Request $request, StudentParent $studentParent): View
    {
        $this->ensureLinkBelongsToTenant($request, $studentParent);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.student_parents.edit', [
            'title' => 'Editar Vínculo',
            'studentParent' => $studentParent,
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'parents' => ParentGuardian::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, StudentParent $studentParent): RedirectResponse
    {
        $this->ensureLinkBelongsToTenant($request, $studentParent);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudentParent($request, $tenantId, $studentParent);
        $studentParent->update($validated);

        return redirect()
            ->route('tenant.student-parents.show', $studentParent)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    public function destroy(Request $request, StudentParent $studentParent): RedirectResponse
    {
        $this->ensureLinkBelongsToTenant($request, $studentParent);
        $studentParent->delete();

        return redirect()
            ->route('tenant.student-parents.index')
            ->with('success', 'Vínculo excluído com sucesso.');
    }

    private function validateStudentParent(Request $request, int $tenantId, ?StudentParent $studentParent = null): array
    {
        return $request->validate([
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

    private function ensureLinkBelongsToTenant(Request $request, StudentParent $link): void
    {
        if ((int) $link->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
