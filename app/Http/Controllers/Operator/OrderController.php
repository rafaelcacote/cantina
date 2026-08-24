<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\School;
use App\Models\Student;
use App\Services\OrderService;
use App\Services\PinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();
        $status = $request->string('status')->toString();
        $paymentMode = $request->string('payment_mode')->toString();
        $search = trim((string) $request->get('search'));

        $orders = Order::query()
            ->with(['school', 'student', 'parent'])
            ->where('tenant_id', $tenantId)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($paymentMode, fn ($q) => $q->where('payment_mode', $paymentMode))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    if (ctype_digit($search)) {
                        $builder->orWhere('id', (int) $search);
                    }
                    $builder
                        ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('parent', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.operator.orders.index', [
            'title' => 'Pedidos',
            'orders' => $orders,
            'statuses' => $this->statuses(),
            'paymentModes' => $this->paymentModes(),
            'status' => $status,
            'paymentMode' => $paymentMode,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();

        return view('pages.operator.orders.create', [
            'title' => 'Novo Pedido (Caixa)',
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->when($schoolId, fn ($q) => $q->whereKey($schoolId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                ->orderBy('name')
                ->get(['id', 'name', 'school_id']),
            'paymentModes' => $this->paymentModes(),
            'defaultSchoolId' => $schoolId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $scopedSchoolId = $user->scopedSchoolId();

        $validated = $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'payment_mode' => ['required', Rule::in(array_keys($this->paymentModes()))],
            'notes' => ['nullable', 'string'],
        ]);

        if ($scopedSchoolId && (int) $validated['school_id'] !== $scopedSchoolId) {
            throw ValidationException::withMessages([
                'school_id' => 'Você só pode criar pedidos para a sua escola.',
            ]);
        }

        $student = Student::query()->whereKey($validated['student_id'])->firstOrFail();
        if ((int) $student->school_id !== (int) $validated['school_id']) {
            throw ValidationException::withMessages([
                'student_id' => 'O aluno não pertence à escola selecionada.',
            ]);
        }

        $order = Order::query()->create([
            'tenant_id' => $tenantId,
            'school_id' => $validated['school_id'],
            'student_id' => $validated['student_id'],
            'parent_id' => null,
            'placed_by_user_id' => $user->id,
            'order_channel' => 'cashier',
            'order_type' => 'immediate',
            'status' => 'pending',
            'payment_mode' => $validated['payment_mode'],
            'total_amount' => 0,
            'discount_amount' => 0,
            'final_amount' => 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('operator.orders.show', $order)
            ->with('success', 'Pedido criado. Adicione os itens.');
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureAccessible($request, $order);
        $order->load([
            'school',
            'student',
            'parent',
            'placedBy',
            'items.product',
            'payments' => fn ($query) => $query->latest(),
        ]);

        return view('pages.operator.orders.show', [
            'title' => 'Pedido #'.$order->id,
            'order' => $order,
            'products' => Product::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'price']),
            'statuses' => $this->statuses(),
            'paymentModes' => $this->paymentModes(),
            'channels' => $this->channels(),
            'types' => $this->types(),
            'pinAlreadyProvided' => app(PinService::class)->orderAlreadyAuthorizedByPin($order),
        ]);
    }

    public function addItem(Request $request, Order $order): RedirectResponse
    {
        $this->ensureAccessible($request, $order);

        if ($order->status !== 'pending') {
            return back()->withErrors(['product_id' => 'Só é possível adicionar itens em pedidos pendentes.']);
        }

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('tenant_id', $order->tenant_id)),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->whereKey($validated['product_id'])->firstOrFail();
        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) $product->price;

        OrderItem::query()->create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_name_snapshot' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => round($unitPrice * $quantity, 2),
            'item_status' => 'pending',
        ]);

        $this->recalculateTotals($order);

        return redirect()
            ->route('operator.orders.show', $order)
            ->with('success', 'Item adicionado.');
    }

    public function updateStatus(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->ensureAccessible($request, $order);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'student_pin' => ['nullable', 'string', 'max:20'],
        ]);

        $orderService->transitionStatus(
            $order,
            $validated['status'],
            $request->user(),
            $validated['student_pin'] ?? null
        );

        return redirect()
            ->route('operator.orders.show', $order)
            ->with('success', 'Status atualizado.');
    }

    private function recalculateTotals(Order $order): void
    {
        $order->refresh();
        $total = (float) $order->items()->sum('total_price');
        $order->update([
            'total_amount' => $total,
            'discount_amount' => 0,
            'final_amount' => $total,
        ]);
    }

    private function ensureAccessible(Request $request, Order $order): void
    {
        $user = $request->user();
        if ((int) $order->tenant_id !== (int) $user->tenant_id) {
            abort(404);
        }

        $schoolId = $user->scopedSchoolId();
        if ($schoolId && (int) $order->school_id !== $schoolId) {
            abort(404);
        }
    }

    private function statuses(): array
    {
        return [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em preparo',
            'ready' => 'Pronto',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
        ];
    }

    private function paymentModes(): array
    {
        return [
            'wallet' => 'Carteira',
            'tab' => 'Fiado',
            'cash' => 'Dinheiro',
            'pix' => 'Pix',
            'card' => 'Cartão',
        ];
    }

    private function channels(): array
    {
        return [
            'app' => 'App',
            'cashier' => 'Caixa',
            'web' => 'Web',
            'totem' => 'Totem',
        ];
    }

    private function types(): array
    {
        return [
            'immediate' => 'Imediato',
            'scheduled' => 'Agendado',
            'custom' => 'Customizado',
        ];
    }
}
