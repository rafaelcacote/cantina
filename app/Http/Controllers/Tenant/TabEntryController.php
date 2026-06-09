<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\TabEntry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TabEntryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $entries = TabEntry::query()
            ->with(['studentTab.student', 'student', 'order', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('entry_date', '<=', $to))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.tab_entries.index', [
            'title' => 'Lançamentos',
            'entries' => $entries,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => $this->statuses(),
            'studentId' => $studentId,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.tab_entries.create', [
            'title' => 'Novo Lançamento',
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'studentTabs' => StudentTab::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->orderBy('id')
                ->get(['id', 'student_id']),
            'orders' => Order::query()
                ->where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->limit(100)
                ->get(['id']),
            'users' => User::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => $this->statuses(),
            'authorizationMethods' => $this->authorizationMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateEntry($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $validated['authorized_by_pin'] = $request->boolean('authorized_by_pin');
        if (empty($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        TabEntry::query()->create($validated);

        return redirect()
            ->route('tenant.tab-entries.index')
            ->with('success', 'Lançamento criado com sucesso.');
    }

    public function show(Request $request, TabEntry $tabEntry): View
    {
        $this->ensureEntryBelongsToTenant($request, $tabEntry);
        $tabEntry->load(['studentTab.student', 'student', 'order', 'creator']);

        return view('pages.tenant.tab_entries.show', [
            'title' => 'Detalhes do Lançamento',
            'entry' => $tabEntry,
            'statuses' => $this->statuses(),
            'authorizationMethods' => $this->authorizationMethods(),
        ]);
    }

    public function edit(Request $request, TabEntry $tabEntry): View
    {
        $this->ensureEntryBelongsToTenant($request, $tabEntry);

        return view('pages.tenant.tab_entries.edit', [
            'title' => 'Editar Lançamento',
            'entry' => $tabEntry,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, TabEntry $tabEntry): RedirectResponse
    {
        $this->ensureEntryBelongsToTenant($request, $tabEntry);
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
        ]);
        $tabEntry->update($validated);

        return redirect()
            ->route('tenant.tab-entries.show', $tabEntry)
            ->with('success', 'Status do lançamento atualizado com sucesso.');
    }

    private function validateEntry(Request $request, int $tenantId): array
    {
        return $request->validate([
            'student_tab_id' => [
                'required',
                'integer',
                Rule::exists('student_tabs', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('student_id', $request->input('student_id'))),
            ],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'order_id' => [
                'nullable',
                'integer',
                Rule::exists('orders', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'authorized_by_pin' => ['nullable', 'boolean'],
            'authorization_method' => ['nullable', Rule::in(array_keys($this->authorizationMethods()))],
            'authorized_at' => ['nullable', 'date'],
            'created_by' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);
    }

    private function ensureEntryBelongsToTenant(Request $request, TabEntry $entry): void
    {
        if ((int) $entry->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function statuses(): array
    {
        return [
            'open' => 'Aberto',
            'paid' => 'Pago',
            'cancelled' => 'Cancelado',
        ];
    }

    private function authorizationMethods(): array
    {
        return [
            'pin' => 'PIN',
            'manual' => 'Manual',
            'biometric' => 'Biometria',
        ];
    }
}
