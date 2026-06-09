<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $parentId = $request->integer('parent_id') ?: null;
        $method = $request->string('payment_method')->toString();
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $payments = Payment::query()
            ->with(['student', 'parent', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($parentId, fn ($query) => $query->where('parent_id', $parentId))
            ->when($method, fn ($query) => $query->where('payment_method', $method))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('paid_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.payments.index', [
            'title' => 'Pagamentos',
            'payments' => $payments,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'parents' => ParentGuardian::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
            'studentId' => $studentId,
            'parentId' => $parentId,
            'method' => $method,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.payments.create', [
            'title' => 'Novo Pagamento',
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'parents' => ParentGuardian::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'users' => User::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validatePayment($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        if (empty($validated['created_by'])) {
            $validated['created_by'] = $request->user()->id;
        }

        $payment = Payment::query()->create($validated);

        return redirect()
            ->route('tenant.payments.show', $payment)
            ->with('success', 'Pagamento criado com sucesso.');
    }

    public function show(Request $request, Payment $payment): View
    {
        $this->ensurePaymentBelongsToTenant($request, $payment);
        $payment->load(['student', 'parent', 'creator']);

        return view('pages.tenant.payments.show', [
            'title' => 'Detalhes do Pagamento',
            'payment' => $payment,
            'methods' => $this->methods(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function edit(Request $request, Payment $payment): View
    {
        $this->ensurePaymentBelongsToTenant($request, $payment);

        return view('pages.tenant.payments.edit', [
            'title' => 'Editar Pagamento',
            'payment' => $payment,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensurePaymentBelongsToTenant($request, $payment);
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
        ]);
        $payment->update($validated);

        return redirect()
            ->route('tenant.payments.show', $payment)
            ->with('success', 'Status do pagamento atualizado com sucesso.');
    }

    private function validatePayment(Request $request, int $tenantId): array
    {
        return $request->validate([
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('parents', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(array_keys($this->methods()))],
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'paid_at' => ['nullable', 'date'],
            'created_by' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);
    }

    private function ensurePaymentBelongsToTenant(Request $request, Payment $payment): void
    {
        if ((int) $payment->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
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
