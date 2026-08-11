<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChildAccessController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(private readonly StudentAccessService $access) {}

    public function show(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.user', 'student.wallet']);

        $student = $link->student;
        $invitation = $student->user_id ? null : $this->access->invitationFor($parent, $student);
        $tenantName = $request->user()->tenant?->name ?? 'Cantina';

        return Inertia::render('Parent/ChildAccess', [
            'child' => $this->presentChild($link),
            'share' => $this->access->sharePayload($student, $invitation, $tenantName),
        ]);
    }
}
