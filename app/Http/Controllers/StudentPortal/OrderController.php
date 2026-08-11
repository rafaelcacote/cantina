<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Student;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $student = $this->resolveStudentOrFail($request);

        return Inertia::render('Student/Checkout', [
            'walletBalance' => (float) ($student->wallet?->balance ?? 0),
            'paymentOptions' => $this->paymentOptions($student),
        ]);
    }

    public function store(Request $request, OrderService $orderService): RedirectResponse
    {
        $student = $this->resolveStudentOrFail($request);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_mode' => ['required', Rule::in(array_column($this->paymentOptions($student), 'value'))],
            'notes' => ['nullable', 'string', 'max:500'],
            'student_pin' => ['nullable', 'string', 'max:20'],
        ]);

        $order = $orderService->placeFromStudentApp(
            $student,
            $request->user(),
            $validated['items'],
            $validated['payment_mode'],
            $validated['notes'] ?? null,
            $validated['student_pin'] ?? null,
        );

        return redirect()
            ->route('student.orders.show', $order)
            ->with('success', 'Pedido enviado. A cantina já recebeu sua solicitação.');
    }

    public function show(Request $request, Order $order): Response
    {
        $student = $this->resolveStudentOrFail($request);
        $this->ensureOwnsOrder($student, $order);

        $order->load('items');

        return Inertia::render('Student/OrderShow', [
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'payment_mode' => $order->payment_mode,
                'total' => (float) $order->final_amount,
                'notes' => $order->notes,
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
                'can_cancel' => $order->status === 'pending',
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->item_name_snapshot,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total_price,
                ])->values()->all(),
            ],
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $student = $this->resolveStudentOrFail($request);
        $this->ensureOwnsOrder($student, $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Só é possível cancelar pedidos ainda pendentes.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()
            ->route('student.orders.show', $order)
            ->with('success', 'Pedido cancelado.');
    }

    private function paymentOptions(Student $student): array
    {
        $options = [
            [
                'value' => 'wallet',
                'label' => 'Carteira',
                'hint' => 'Usar o saldo da sua carteira',
            ],
            [
                'value' => 'cash',
                'label' => 'Pagar na cantina',
                'hint' => 'Você paga quando retirar o pedido',
            ],
        ];

        if ($student->can_buy_on_tab) {
            $options[] = [
                'value' => 'tab',
                'label' => 'Fiado',
                'hint' => 'Lançar na conta e informar o PIN',
            ];
        }

        return $options;
    }

    private function resolveStudentOrFail(Request $request): Student
    {
        $student = Student::forPortalUser($request->user());

        if (! $student) {
            abort(403, 'Aluno não vinculado a este usuário.');
        }

        return $student;
    }

    private function ensureOwnsOrder(Student $student, Order $order): void
    {
        if ((int) $order->tenant_id !== (int) $student->tenant_id || (int) $order->student_id !== (int) $student->id) {
            abort(404);
        }
    }
}
