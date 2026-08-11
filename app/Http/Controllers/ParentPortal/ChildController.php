<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ParentalControl;
use App\Models\School;
use App\Models\Student;
use App\Models\TabEntry;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Services\ParentRegistrationService;
use App\Services\PinService;
use App\Services\TabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ChildController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(
        private readonly ParentRegistrationService $registration,
        private readonly PinService $pins,
        private readonly TabService $tabs,
    ) {}

    public function index(Request $request): Response
    {
        $parent = $this->parentFor($request);
        $children = $this->linksFor($parent)
            ->map(fn ($link) => $this->presentChild($link))
            ->values();

        return Inertia::render('Parent/Children', [
            'children' => $children,
        ]);
    }

    public function create(Request $request): Response
    {
        $parent = $this->parentFor($request);

        $schools = School::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Parent/ChildCreate', [
            'schools' => $schools,
            'relationshipTypes' => ParentRegistrationService::RELATIONSHIP_TYPES,
            'shifts' => ParentRegistrationService::SHIFTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $parent = $this->parentFor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $parent->tenant_id)),
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'grade' => ['nullable', 'string', 'max:50'],
            'classroom' => ['nullable', 'string', 'max:50'],
            'shift' => ['nullable', 'string', Rule::in(ParentRegistrationService::SHIFTS)],
            'relationship_type' => ['nullable', 'string', Rule::in(ParentRegistrationService::RELATIONSHIP_TYPES)],
        ]);

        $student = $this->registration->addChild($parent, $validated);

        return redirect()
            ->route('parent.children.show', $student)
            ->with('success', 'Filho cadastrado. A cantina ainda precisa confirmar o cadastro.');
    }

    public function show(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.wallet', 'student.tab']);
        $student->loadMissing('tab');

        $orders = Order::query()
            ->with('items')
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Order $order) => $this->presentOrder($order))
            ->values()
            ->all();

        $transactions = WalletTransaction::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (WalletTransaction $tx) => [
                'id' => $tx->id,
                'type' => $tx->transaction_type,
                'amount' => (float) $tx->amount,
                'description' => $tx->description,
                'created_at' => $tx->created_at?->format('d/m · H:i'),
            ])
            ->values()
            ->all();

        $topups = WalletTopup::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (WalletTopup $topup) => [
                'id' => $topup->id,
                'code' => $topup->code,
                'amount' => (float) $topup->amount,
                'status' => $topup->status,
                'created_at' => $topup->created_at?->format('d/m · H:i'),
            ])
            ->values()
            ->all();

        $month = $this->tabs->resolveMonth(null);
        $monthEntries = TabEntry::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('student_id', $student->id)
            ->whereDate('entry_date', '>=', $month['start'])
            ->whereDate('entry_date', '<=', $month['end'])
            ->get();

        return Inertia::render('Parent/ChildShow', [
            'child' => $this->presentChild($link),
            'orders' => $orders,
            'transactions' => $transactions,
            'topups' => $topups,
            'canDeposit' => filled($request->user()->tenant?->pix),
            'tab' => [
                'month' => $month,
                'summary' => $this->tabs->summarizeEntries($monthEntries),
                'open_balance' => (float) ($student->tab?->current_balance ?? 0),
            ],
        ]);
    }

    public function edit(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $link->load(['student.school', 'student.wallet']);

        $schools = School::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where(fn ($query) => $query->where('active', true)->orWhere('id', $student->school_id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Parent/ChildEdit', [
            'child' => $this->presentChild($link),
            'form' => [
                'name' => $student->name,
                'school_id' => $student->school_id,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'grade' => $student->grade,
                'classroom' => $student->classroom,
                'shift' => $student->shift,
                'relationship_type' => $link->relationship_type ?: 'Responsável',
                'can_buy_on_tab' => (bool) $student->can_buy_on_tab,
            ],
            'pin' => $this->pins->reveal($student),
            'has_pin' => $this->pins->hasPin($student),
            'schools' => $schools,
            'relationshipTypes' => ParentRegistrationService::RELATIONSHIP_TYPES,
            'shifts' => ParentRegistrationService::SHIFTS,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $parent->tenant_id)),
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'grade' => ['nullable', 'string', 'max:50'],
            'classroom' => ['nullable', 'string', 'max:50'],
            'shift' => ['nullable', 'string', Rule::in(ParentRegistrationService::SHIFTS)],
            'relationship_type' => ['nullable', 'string', Rule::in(ParentRegistrationService::RELATIONSHIP_TYPES)],
            'can_buy_on_tab' => ['required', 'boolean'],
            'personal_pin' => ['nullable', 'digits_between:4,8'],
            'personal_pin_confirmation' => ['required_with:personal_pin', 'same:personal_pin'],
        ]);

        $wantsTab = (bool) $validated['can_buy_on_tab'];
        $newPin = is_string($validated['personal_pin'] ?? null) ? trim((string) $validated['personal_pin']) : '';

        if ($wantsTab && ! $this->pins->hasPin($student) && $newPin === '') {
            throw ValidationException::withMessages([
                'personal_pin' => 'Defina um PIN para liberar a compra no fiado.',
            ]);
        }

        $student->update([
            'name' => $validated['name'],
            'school_id' => $validated['school_id'],
            'birth_date' => $validated['birth_date'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'classroom' => $validated['classroom'] ?? null,
            'shift' => $validated['shift'] ?? null,
            'can_buy_on_tab' => $wantsTab,
        ]);

        $link->update([
            'relationship_type' => $validated['relationship_type'] ?? $link->relationship_type,
        ]);

        if ($newPin !== '') {
            $this->pins->assign($student, $newPin);
        }

        if ($wantsTab) {
            $this->tabs->ensureForStudent($student);
        }

        $control = ParentalControl::query()
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->id)
            ->first();

        if ($control) {
            $control->update(['allow_tab_usage' => $wantsTab]);
        }

        return redirect()
            ->route('parent.children.show', $student)
            ->with('success', 'Dados do filho atualizados.');
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'status' => $order->status,
            'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
            'created_at' => $order->created_at?->format('d/m · H:i'),
            'item_count' => $order->relationLoaded('items') ? $order->items->count() : 0,
            'preview' => $order->relationLoaded('items')
                ? $order->items->take(2)->pluck('item_name_snapshot')->filter()->implode(', ')
                : null,
        ];
    }
}
