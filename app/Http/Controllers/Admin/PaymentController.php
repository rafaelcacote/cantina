<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $parentId = $request->integer('parent_id') ?: null;
        $method = $request->string('payment_method')->toString();
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $payments = Payment::query()
            ->with(['student', 'parent', 'creator'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($parentId, fn ($query) => $query->where('parent_id', $parentId))
            ->when($method, fn ($query) => $query->where('payment_method', $method))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('paid_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.payments.index', [
            'title' => 'Pagamentos',
            'payments' => $payments,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'parents' => ParentGuardian::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'parentId' => $parentId,
            'method' => $method,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.payments.create', [
            'title' => 'Novo Pagamento',
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'parents' => ParentGuardian::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'users' => User::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $payment = Payment::create($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Pagamento criado com sucesso.');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['student', 'parent', 'creator']);

        return view('pages.admin.payments.show', [
            'title' => 'Detalhes do Pagamento',
            'payment' => $payment,
            'tenantName' => DB::table('tenants')->where('id', $payment->tenant_id)->value('name'),
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function edit(Payment $payment): View
    {
        return view('pages.admin.payments.edit', [
            'title' => 'Editar Pagamento',
            'payment' => $payment,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
        ]);
        $payment->update($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Status do pagamento atualizado com sucesso.');
    }

    private function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'student_id' => ['nullable', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'parent_id' => ['nullable', 'integer', Rule::exists('parents', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(array_keys($this->methods()))],
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'paid_at' => ['nullable', 'date'],
            'created_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', request('tenant_id')))],
        ];
    }

    private function methods(): array
    {
        return [
            'cash' => 'Dinheiro',
            'pix' => 'Pix',
            'card' => 'Cartão',
            'wallet' => 'Carteira',
            'tab' => 'Fiado',
        ];
    }

    private function statuses(): array
    {
        return [
            'pending' => 'Pendente',
            'completed' => 'Concluído',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
        ];
    }
}
