<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ResolvesParentProfile
{
    protected function parentFor(Request $request): ParentGuardian
    {
        $parent = ParentGuardian::forPortalUser($request->user());

        if (! $parent) {
            abort(403, 'Responsável não vinculado a este usuário.');
        }

        return $parent;
    }

    /**
     * @return Collection<int, StudentParent>
     */
    protected function linksFor(ParentGuardian $parent): Collection
    {
        return StudentParent::query()
            ->with(['student.school', 'student.wallet', 'student.tab'])
            ->where('tenant_id', $parent->tenant_id)
            ->where('parent_id', $parent->id)
            ->latest()
            ->get();
    }

    protected function ensureOwnsStudent(ParentGuardian $parent, Student $student): StudentParent
    {
        $link = StudentParent::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $link) {
            abort(404);
        }

        return $link;
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentChild(StudentParent $link): array
    {
        $student = $link->student;

        return [
            'id' => $student?->id,
            'name' => $student?->name ?? 'Aluno',
            'school' => $student?->school?->name,
            'enrollment' => $student?->enrollment_number,
            'grade' => $student?->grade,
            'classroom' => $student?->classroom,
            'shift' => $student?->shift,
            'birth_date' => $student?->birth_date?->format('d/m/Y'),
            'status' => $student?->status ?? 'pending',
            'relationship' => $link->relationship_type,
            'balance' => (float) ($student?->wallet?->balance ?? 0),
            'tab_balance' => (float) ($student?->tab?->current_balance ?? 0),
            'has_access' => (bool) $student?->user_id,
            'can_buy_on_tab' => (bool) $student?->can_buy_on_tab,
            'has_pin' => $student
                ? (filled($student->getRawOriginal('personal_pin')) || filled($student->personal_pin_hash))
                : false,
            'can_order' => $student
                && $student->status === 'active'
                && filled($student->school_id),
        ];
    }
}
