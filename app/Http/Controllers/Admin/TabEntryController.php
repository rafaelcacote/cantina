<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\TabEntry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TabEntryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $entries = TabEntry::query()
            ->with(['studentTab', 'student', 'order', 'creator'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('entry_date', '<=', $to))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.tab_entries.index', [
            'title' => 'Lançamentos',
            'entries' => $entries,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'statuses' => $this->statuses(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.tab_entries.create', [
            'title' => 'Novo Lançamento',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'studentTabs' => StudentTab::query()->select(['id', 'tenant_id', 'student_id', 'current_balance'])->orderBy('id')->get(),
            'orders' => Order::query()->select(['id', 'tenant_id'])->orderByDesc('id')->get(),
            'users' => User::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'statuses' => $this->statuses(),
            'authorizationMethods' => $this->authorizationMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['authorized_by_pin'] = $request->boolean('authorized_by_pin');

        DB::transaction(function () use ($validated) {
            $entry = TabEntry::create($validated);
            $entry->studentTab?->recalculateBalance();
        });

        return redirect()
            ->route('admin.tab-entries.index')
            ->with('success', 'Lançamento criado com sucesso.');
    }

    public function show(TabEntry $tabEntry): View
    {
        $tabEntry->load(['studentTab', 'student', 'order', 'creator']);

        return view('pages.admin.tab_entries.show', [
            'title' => 'Detalhes do Lançamento',
            'entry' => $tabEntry,
            'tenantName' => DB::table('tenants')->where('id', $tabEntry->tenant_id)->value('name'),
            'statuses' => $this->statuses(),
        ]);
    }

    public function edit(TabEntry $tabEntry): View
    {
        return view('pages.admin.tab_entries.edit', [
            'title' => 'Editar Lançamento',
            'entry' => $tabEntry,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, TabEntry $tabEntry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
        ]);

        DB::transaction(function () use ($tabEntry, $validated) {
            $tabEntry->update($validated);
            $tabEntry->studentTab?->recalculateBalance();
        });

        return redirect()
            ->route('admin.tab-entries.show', $tabEntry)
            ->with('success', 'Status do lançamento atualizado com sucesso.');
    }

    public function destroy(TabEntry $tabEntry): RedirectResponse
    {
        DB::transaction(function () use ($tabEntry) {
            $studentTab = $tabEntry->studentTab;
            $tabEntry->delete();
            $studentTab?->recalculateBalance();
        });

        return redirect()
            ->route('admin.tab-entries.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }

    private function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_tab_id' => [
                'required',
                'integer',
                Rule::exists('student_tabs', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', request('tenant_id'))
                    ->where('student_id', request('student_id'))),
            ],
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'authorized_by_pin' => ['nullable', 'boolean'],
            'authorization_method' => ['nullable', Rule::in(array_keys($this->authorizationMethods()))],
            'authorized_at' => ['nullable', 'date'],
            'created_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
        ];
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
