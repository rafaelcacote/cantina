<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Services\PinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const STATUS_OPTIONS = [
        'pending' => 'Pendente',
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'blocked' => 'Bloqueado',
    ];

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

    public function store(Request $request, PinService $pinService): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudent($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['photo_url'] = $this->storePhoto($request, $tenantId);
        $validated = $this->applyPin($validated, $pinService);

        unset($validated['photo']);

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
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function parents(Request $request, Student $student): View|JsonResponse
    {
        $this->ensureStudentBelongsToTenant($request, $student);

        if ($request->wantsJson() || $request->ajax()) {
            $links = $student->studentParents()
                ->with(['parent'])
                ->latest()
                ->get()
                ->map(fn ($link) => [
                    'id' => $link->id,
                    'parent_name' => $link->parent?->name ?? '-',
                    'phone' => $link->parent?->phone ?? '-',
                    'relationship_type' => $link->relationship_type ?: '-',
                    'is_primary' => (bool) $link->is_primary,
                    'financial_responsible' => (bool) $link->financial_responsible,
                    'show_url' => route('tenant.student-parents.show', $link),
                    'edit_url' => route('tenant.student-parents.edit', $link),
                ]);

            return response()->json([
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
                'links' => $links,
                'create_url' => route('tenant.student-parents.create', ['student_id' => $student->id]),
            ]);
        }

        $links = $student->studentParents()
            ->with(['parent'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.tenant.students.parents', [
            'title' => 'Responsáveis do Aluno',
            'student' => $student,
            'links' => $links,
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

    public function update(Request $request, Student $student, PinService $pinService): RedirectResponse
    {
        $this->ensureStudentBelongsToTenant($request, $student);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateStudent($request, $tenantId, $student);

        if ($request->hasFile('photo')) {
            $this->deleteStoredPhoto($student->photo_url);
            $validated['photo_url'] = $this->storePhoto($request, $tenantId);
        }

        $validated = $this->applyPin($validated, $pinService);

        unset($validated['photo']);

        $student->update($validated);

        return redirect()
            ->route('tenant.students.show', $student)
            ->with('success', 'Aluno atualizado com sucesso.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->ensureStudentBelongsToTenant($request, $student);

        if ($student->orders()->exists()) {
            return back()->withErrors([
                'delete' => 'Não é possível excluir o aluno enquanto houver pedidos vinculados.',
            ]);
        }

        $student->delete();

        return redirect()
            ->route('tenant.students.index')
            ->with('success', 'Aluno excluído com sucesso.');
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
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'personal_pin' => ['nullable', 'string', 'max:20'],
            'can_buy_on_credit' => ['required', 'boolean'],
            'can_buy_on_tab' => ['required', 'boolean'],
            'convenience_access' => ['required', 'boolean'],
            'snack_access' => ['required', 'boolean'],
        ]);
    }

    private function applyPin(array $validated, PinService $pinService): array
    {
        return $pinService->applyToPayload($validated, $validated['personal_pin'] ?? null);
    }

    private function storePhoto(Request $request, int $tenantId): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store("students/{$tenantId}", 'public');
    }

    private function deleteStoredPhoto(?string $photoUrl): void
    {
        if (! $photoUrl || str_starts_with($photoUrl, 'http')) {
            return;
        }

        Storage::disk('public')->delete($photoUrl);
    }

    private function ensureStudentBelongsToTenant(Request $request, Student $student): void
    {
        if ((int) $student->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
