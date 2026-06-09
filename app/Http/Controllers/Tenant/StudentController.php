<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const STATUS_OPTIONS = ['pending', 'active', 'inactive', 'blocked'];

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));
        $schoolId = $request->integer('school_id') ?: null;
        $status = $request->string('status')->toString();

        $students = Student::query()
            ->with('school')
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('enrollment_number', 'like', "%{$search}%");
                });
            })
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.tenant.students.index', [
            'title' => 'Alunos',
            'students' => $students,
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statusOptions' => self::STATUS_OPTIONS,
            'search' => $search,
            'schoolId' => $schoolId,
            'status' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.students.create', [
            'title' => 'Novo Aluno',
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudent($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $validated['status'] = $validated['status'] ?? 'pending';

        $student = Student::query()->create($validated);

        return redirect()
            ->route('tenant.students.show', $student)
            ->with('success', 'Aluno criado com sucesso.');
    }

    public function show(Request $request, Student $student): View
    {
        $this->ensureStudentBelongsToTenant($request, $student);
        $student->load('school');

        return view('pages.tenant.students.show', [
            'title' => 'Detalhes do Aluno',
            'student' => $student,
        ]);
    }

    public function edit(Request $request, Student $student): View
    {
        $this->ensureStudentBelongsToTenant($request, $student);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.students.edit', [
            'title' => 'Editar Aluno',
            'student' => $student,
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->ensureStudentBelongsToTenant($request, $student);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudent($request, $tenantId, $student);

        $student->update($validated);

        return redirect()
            ->route('tenant.students.show', $student)
            ->with('success', 'Aluno atualizado com sucesso.');
    }

    private function validateStudent(Request $request, int $tenantId, ?Student $student = null): array
    {
        return $request->validate([
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
            'status' => ['nullable', Rule::in(self::STATUS_OPTIONS)],
            'photo_url' => ['nullable', 'string', 'max:1000'],
            'personal_pin_hash' => ['nullable', 'string', 'max:255'],
            'can_buy_on_credit' => ['required', 'boolean'],
            'can_buy_on_tab' => ['required', 'boolean'],
            'convenience_access' => ['required', 'boolean'],
            'snack_access' => ['required', 'boolean'],
        ]);
    }

    private function ensureStudentBelongsToTenant(Request $request, Student $student): void
    {
        if ((int) $student->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
