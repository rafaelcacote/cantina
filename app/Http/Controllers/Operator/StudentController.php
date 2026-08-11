<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();
        $search = trim((string) $request->get('search'));

        $students = Student::query()
            ->with(['school', 'wallet', 'tab'])
            ->where('tenant_id', $tenantId)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('enrollment_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.operator.students.index', [
            'title' => 'Consulta de Alunos',
            'students' => $students,
            'search' => $search,
            'schoolScoped' => (bool) $schoolId,
        ]);
    }

    public function show(Request $request, Student $student): View
    {
        $this->ensureAccessible($request, $student);
        $student->load(['school', 'wallet.transactions' => fn ($q) => $q->latest()->limit(10), 'tab.entries' => fn ($q) => $q->latest()->limit(10)]);

        return view('pages.operator.students.show', [
            'title' => 'Aluno',
            'student' => $student,
        ]);
    }

    private function ensureAccessible(Request $request, Student $student): void
    {
        $user = $request->user();
        if ((int) $student->tenant_id !== (int) $user->tenant_id) {
            abort(404);
        }

        $schoolId = $user->scopedSchoolId();
        if ($schoolId && (int) $student->school_id !== $schoolId) {
            abort(404);
        }
    }
}
