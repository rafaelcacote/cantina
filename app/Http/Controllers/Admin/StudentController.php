<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const STATUS_OPTIONS = ['pending', 'active', 'inactive', 'blocked'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $tenantId = $request->get('tenant_id');
        $schoolId = $request->get('school_id');

        $students = Student::query()
            ->with(['tenant', 'school'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('enrollment_number', 'like', "%{$search}%");
                });
            })
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.students.index', [
            'title' => 'Alunos',
            'students' => $students,
            'search' => $search,
            'tenantId' => $tenantId,
            'schoolId' => $schoolId,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'schools' => School::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.students.create', [
            'title' => 'Novo Aluno',
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'schools' => School::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudent($request);

        Student::query()->create($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Aluno criado com sucesso.');
    }

    public function show(Student $student): View
    {
        $student->load(['tenant', 'school']);

        return view('pages.admin.students.show', [
            'title' => 'Detalhes do Aluno',
            'student' => $student,
        ]);
    }

    public function edit(Student $student): View
    {
        return view('pages.admin.students.edit', [
            'title' => 'Editar Aluno',
            'student' => $student,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'schools' => School::query()->orderBy('name')->get(['id', 'tenant_id', 'name']),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $this->validateStudent($request, $student);

        $student->update($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Aluno atualizado com sucesso.');
    }

    private function validateStudent(Request $request, ?Student $student = null): array
    {
        $tenantId = (int) $request->input('tenant_id');

        return $request->validate([
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'enrollment_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'enrollment_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($student?->id),
            ],
            'birth_date' => ['nullable', 'date'],
            'grade' => ['nullable', 'string', 'max:100'],
            'classroom' => ['nullable', 'string', 'max:100'],
            'shift' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'photo_url' => ['nullable', 'string', 'max:1000'],
            'personal_pin_hash' => ['nullable', 'string', 'max:255'],
            'can_buy_on_credit' => ['required', 'boolean'],
            'can_buy_on_tab' => ['required', 'boolean'],
            'convenience_access' => ['required', 'boolean'],
            'snack_access' => ['required', 'boolean'],
        ]);
    }
}
