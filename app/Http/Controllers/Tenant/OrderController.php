<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ParentGuardian;
use App\Models\Product;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
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
        $tenantId = $request->user()->tenant_id;
        $search = trim((string) $request->get('search'));
        $schoolId = $request->integer('school_id') ?: null;
        $status = $request->string('status')->toString();
        $paymentMode = $request->string('payment_mode')->toString();

        $orders = Order::query()
            ->with(['school', 'student', 'parent'])
            ->where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    if (ctype_digit($search)) {
                        $builder->orWhere('id', (int) $search);
                    }

                    $builder
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($paymentMode, fn ($query) => $query->where('payment_mode', $paymentMode))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.orders.index', [
            'title' => 'Pedidos',
            'orders' => $orders,
            'schools' => School::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'search' => $search,
            'schoolId' => $schoolId,
            'status' => $status,
            'paymentMode' => $paymentMode,
            'statuses' => $this->statuses(),
            'paymentModes' => $this->paymentModes(),
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.orders.create', [
            'title' => 'Novo Pedido',
            'schools' => School::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'parents' => ParentGuardian::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name', 'email']),
            'channels' => $this->channels(),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
            'paymentModes' => $this->paymentModes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateOrder($request, $tenantId);
        $validated['tenant_id'] = $tenantId;
        $payload = $this->prepareOrderPayload($validated);
        $order = Order::query()->create($payload);

        return redirect()
            ->route('tenant.orders.show', $order)
            ->with('success', 'Pedido criado com sucesso.');
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $order->load([
            'school',
            'student',
            'parent',
            'placedBy',
            'items.product',
            'payments' => fn ($query) => $query->latest(),
        ]);

        return view('pages.tenant.orders.show', [
            'title' => 'Detalhes do Pedido',
            'order' => $order,
            'products' => Product::query()
                ->where('tenant_id', $order->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name', 'price']),
            'statuses' => $this->statuses(),
            'itemStatuses' => $this->itemStatuses(),
            'paymentModes' => $this->paymentModes(),
            'channels' => $this->channels(),
            'types' => $this->types(),
            'pinAlreadyProvided' => app(PinService::class)->orderAlreadyAuthorizedByPin($order),
        ]);
    }

    public function edit(Request $request, Order $order): View
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.orders.edit', [
            'title' => 'Editar Pedido',
            'order' => $order,
            'schools' => School::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'students' => Student::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'parents' => ParentGuardian::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name', 'email']),
            'channels' => $this->channels(),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
            'paymentModes' => $this->paymentModes(),
        ]);
    }

    public function update(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $tenantId = $request->user()->tenant_id;
        $validated = $this->validateOrder($request, $tenantId);
        $payload = $this->prepareOrderPayload($validated);
        $newStatus = $payload['status'] ?? $order->status;
        unset($payload['status']);

        $order->update($payload);

        if ($newStatus !== $order->status) {
            $orderService->transitionStatus(
                $order->fresh(),
                $newStatus,
                $request->user(),
                $request->input('student_pin')
            );
        }

        return redirect()
            ->route('tenant.orders.show', $order)
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function addItem(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $validated = $request->validate($this->itemRules($order));
        $product = Product::query()->whereKey($validated['product_id'])->firstOrFail();
        $unitPrice = isset($validated['unit_price']) ? (float) $validated['unit_price'] : (float) $product->price;
        $quantity = (int) $validated['quantity'];
        $totalPrice = round($unitPrice * $quantity, 2);

        OrderItem::query()->create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_name_snapshot' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'observation' => $validated['observation'] ?? null,
            'custom_request_text' => $validated['custom_request_text'] ?? null,
            'item_status' => $validated['item_status'],
        ]);

        $this->recalculateTotals($order);

        return redirect()
            ->route('tenant.orders.show', $order)
            ->with('success', 'Item adicionado ao pedido com sucesso.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $this->ensureItemBelongsToOrder($order, $item);
        $validated = $request->validate($this->itemRules($order));
        $product = Product::query()->whereKey($validated['product_id'])->firstOrFail();
        $unitPrice = (float) $validated['unit_price'];
        $quantity = (int) $validated['quantity'];

        $item->update([
            'product_id' => $product->id,
            'item_name_snapshot' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => round($unitPrice * $quantity, 2),
            'observation' => $validated['observation'] ?? null,
            'custom_request_text' => $validated['custom_request_text'] ?? null,
            'item_status' => $validated['item_status'],
        ]);

        $this->recalculateTotals($order);

        return redirect()
            ->route('tenant.orders.show', $order)
            ->with('success', 'Item do pedido atualizado com sucesso.');
    }

    public function removeItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $this->ensureItemBelongsToOrder($order, $item);
        $item->delete();
        $this->recalculateTotals($order);

        return redirect()
            ->route('tenant.orders.show', $order)
            ->with('success', 'Item removido do pedido com sucesso.');
    }

    public function updateStatus(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
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
            ->route('tenant.orders.show', $order)
            ->with('success', 'Status do pedido atualizado com sucesso.');
    }

    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderBelongsToTenant($request, $order);
        $order->delete();

        return redirect()
            ->route('tenant.orders.index')
            ->with('success', 'Pedido excluído com sucesso.');
    }

    private function validateOrder(Request $request, int $tenantId): array
    {
        return $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
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
            'placed_by_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'order_channel' => ['required', Rule::in(array_keys($this->channels()))],
            'order_type' => ['required', Rule::in(array_keys($this->types()))],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'payment_mode' => ['nullable', Rule::in(array_keys($this->paymentModes()))],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'scheduled_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function itemRules(Order $order): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $order->tenant_id)),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'observation' => ['nullable', 'string'],
            'custom_request_text' => ['nullable', 'string'],
            'item_status' => ['required', Rule::in(array_keys($this->itemStatuses()))],
        ];
    }

    private function prepareOrderPayload(array $validated): array
    {
        $totalAmount = (float) ($validated['total_amount'] ?? 0);
        $discountAmount = (float) ($validated['discount_amount'] ?? 0);

        if ($discountAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'discount_amount' => 'O desconto não pode ser maior que o total.',
            ]);
        }

        $finalAmount = round($totalAmount - $discountAmount, 2);
        if ($finalAmount < 0) {
            throw ValidationException::withMessages([
                'total_amount' => 'O valor final não pode ser negativo.',
            ]);
        }

        $validated['total_amount'] = $totalAmount;
        $validated['discount_amount'] = $discountAmount;
        $validated['final_amount'] = $finalAmount;

        return $validated;
    }

    private function recalculateTotals(Order $order): void
    {
        $order->refresh();
        $totalAmount = (float) $order->items()->sum('total_price');
        $discountAmount = (float) $order->discount_amount;
        if ($discountAmount > $totalAmount) {
            $discountAmount = $totalAmount;
        }

        $finalAmount = round($totalAmount - $discountAmount, 2);
        if ($finalAmount < 0) {
            $finalAmount = 0;
        }

        $order->update([
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);
    }

    private function ensureOrderBelongsToTenant(Request $request, Order $order): void
    {
        if ((int) $order->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function ensureItemBelongsToOrder(Order $order, OrderItem $item): void
    {
        if ((int) $item->order_id !== (int) $order->id || (int) $item->tenant_id !== (int) $order->tenant_id) {
            abort(404);
        }
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

    private function itemStatuses(): array
    {
        return [
            'pending' => 'Pendente',
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
}
