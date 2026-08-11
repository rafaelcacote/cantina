<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TabEntry;
use App\Services\TabService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TabController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(private readonly TabService $tabs) {}

    public function index(Request $request): Response
    {
        $parent = $this->parentFor($request);
        $links = $this->linksFor($parent);
        $studentIds = $links->pluck('student_id')->filter()->map(fn ($id) => (int) $id)->all();
        $month = $this->tabs->resolveMonth($request->string('month')->toString() ?: null);
        $selectedStudentId = $request->integer('student_id') ?: null;

        if ($selectedStudentId && ! in_array($selectedStudentId, $studentIds, true)) {
            abort(404);
        }

        $entries = $studentIds
            ? TabEntry::query()
                ->with(['order.items', 'student'])
                ->where('tenant_id', $parent->tenant_id)
                ->whereIn('student_id', $studentIds)
                ->when($selectedStudentId, fn ($query) => $query->where('student_id', $selectedStudentId))
                ->whereDate('entry_date', '>=', $month['start'])
                ->whereDate('entry_date', '<=', $month['end'])
                ->latest('entry_date')
                ->latest('id')
                ->get()
            : collect();

        $byStudent = $entries
            ->groupBy('student_id')
            ->map(function ($group, $studentId) use ($links) {
                $child = $links->firstWhere('student_id', (int) $studentId)?->student;

                return [
                    'id' => (int) $studentId,
                    'name' => $child?->name ?? 'Aluno',
                    'tab_balance' => (float) ($child?->tab?->current_balance ?? 0),
                    'summary' => $this->tabs->summarizeEntries($group),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Parent/Tabs', [
            'month' => $month,
            'summary' => $this->tabs->summarizeEntries($entries),
            'children' => $links->map(fn ($link) => $this->presentChild($link))->values()->all(),
            'selected_student_id' => $selectedStudentId,
            'by_student' => $byStudent,
            'entries' => $entries->map(fn (TabEntry $entry) => $this->tabs->presentEntry($entry))->values()->all(),
        ]);
    }

    public function show(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.wallet', 'student.tab']);

        $month = $this->tabs->resolveMonth($request->string('month')->toString() ?: null);

        $entries = TabEntry::query()
            ->with(['order.items', 'student'])
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->whereDate('entry_date', '>=', $month['start'])
            ->whereDate('entry_date', '<=', $month['end'])
            ->latest('entry_date')
            ->latest('id')
            ->get();

        return Inertia::render('Parent/ChildTab', [
            'child' => $this->presentChild($link),
            'month' => $month,
            'summary' => $this->tabs->summarizeEntries($entries),
            'tab_balance' => (float) ($student->tab?->current_balance ?? 0),
            'entries' => $entries->map(fn (TabEntry $entry) => $this->tabs->presentEntry($entry))->values()->all(),
        ]);
    }
}
