<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Services\AppMenuService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(
        private readonly AppMenuService $menu,
    ) {}

    public function index(Request $request): Response
    {
        $parent = $this->parentFor($request);
        $links = $this->linksFor($parent);
        $studentIds = $links->pluck('student_id')->filter()->all();

        if ($parent->self_student_id) {
            $studentIds[] = (int) $parent->self_student_id;
        }

        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        $orders = $studentIds
            ? Order::query()
                ->with(['items', 'student'])
                ->where('tenant_id', $parent->tenant_id)
                ->whereIn('student_id', $studentIds)
                ->latest()
                ->limit(40)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
                    'created_at' => $order->created_at?->format('d/m · H:i'),
                    'item_count' => $order->items->count(),
                    'preview' => $order->items->take(2)->pluck('item_name_snapshot')->filter()->implode(', '),
                    'student_name' => ((int) $order->student_id === (int) $parent->self_student_id)
                        ? 'Você'
                        : $order->student?->name,
                ])
                ->values()
                ->all()
            : [];

        $children = $links
            ->map(fn ($link) => $this->presentChild($link))
            ->filter(fn (array $child) => $child['can_order'] ?? false)
            ->values()
            ->all();

        return Inertia::render('Parent/Orders', [
            'orders' => $orders,
            'children' => $children,
            'canOrderForSelf' => true,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $parent = $this->parentFor($request);
        $children = $this->linksFor($parent)
            ->map(fn ($link) => $this->presentChild($link))
            ->filter(fn (array $child) => $child['can_order'] ?? false)
            ->values();

        if ($children->count() === 1) {
            return redirect()->route('parent.children.menu', $children->first()['id']);
        }

        return Inertia::render('Parent/OrderCreate', [
            'children' => $children->all(),
        ]);
    }

    public function menu(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $this->ensureStudentCanOrder($student);

        return Inertia::render('Parent/Menu', [
            'dateLabel' => now()->format('d/m/Y'),
            'menuTitle' => 'Produtos da cantina',
            'items' => $this->menu->catalogForStudent($student, (int) $parent->tenant_id),
            'child' => $this->presentChild($link),
            'checkoutHref' => route('parent.children.orders.create', $student, false),
            'cartKey' => 'parent-cart-'.$student->id,
        ]);
    }

    public function checkout(Request $request, Student $student): Response
    {
        $parent = $this->parentFor($request);
        $link = $this->ensureOwnsStudent($parent, $student);
        $this->ensureStudentCanOrder($student);
        $student->loadMissing('wallet');

        return Inertia::render('Parent/Checkout', [
            'walletBalance' => (float) ($student->wallet?->balance ?? 0),
            'paymentOptions' => $this->paymentOptions($student),
            'child' => $this->presentChild($link),
            'menuHref' => route('parent.children.menu', $student, false),
            'storeHref' => route('parent.children.orders.store', $student, false),
            'cartKey' => 'parent-cart-'.$student->id,
        ]);
    }

    public function store(Request $request, Student $student, OrderService $orderService): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsStudent($parent, $student);
        $this->ensureStudentCanOrder($student);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_mode' => ['required', Rule::in(array_column($this->paymentOptions($student), 'value'))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $orderService->placeFromParentApp(
            $parent,
            $student,
            $request->user(),
            $validated['items'],
            $validated['payment_mode'],
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('parent.orders.show', $order)
            ->with('success', 'Pedido enviado em nome de '.$student->name.'.');
    }

    public function show(Request $request, Order $order): Response
    {
        $parent = $this->parentFor($request);
        $this->ensureOwnsOrder($parent, $order);

        $order->load(['items', 'student']);

        return Inertia::render('Parent/OrderShow', [
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'payment_mode' => $order->payment_mode,
                'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
                'notes' => $order->notes,
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
                'student_name' => $order->student?->name,
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
        $parent = $this->parentFor($request);
        $this->ensureOwnsOrder($parent, $order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Só é possível cancelar pedidos ainda pendentes.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()
            ->route('parent.orders.show', $order)
            ->with('success', 'Pedido cancelado.');
    }

    /**
     * @return array<int, array{value: string, label: string, hint: string}>
     */
    private function paymentOptions(Student $student): array
    {
        $options = [
            [
                'value' => 'wallet',
                'label' => 'Carteira',
                'hint' => 'Usar o saldo da carteira do filho',
            ],
            [
                'value' => 'cash',
                'label' => 'Pagar na cantina',
                'hint' => 'Pagar quando o pedido for retirado',
            ],
        ];

        if ($student->can_buy_on_tab) {
            $options[] = [
                'value' => 'tab',
                'label' => 'Fiado',
                'hint' => 'Lançar na conta do filho',
            ];
        }

        return $options;
    }

    private function ensureStudentCanOrder(Student $student): void
    {
        if ($student->status !== 'active' || ! $student->school_id) {
            abort(403, 'A cantina ainda precisa confirmar o cadastro deste aluno.');
        }
    }

    private function ensureOwnsOrder(ParentGuardian $parent, Order $order): void
    {
        $studentIds = $this->linksFor($parent)->pluck('student_id')->filter()->all();

        if ($parent->self_student_id) {
            $studentIds[] = (int) $parent->self_student_id;
        }

        if (
            (int) $order->tenant_id !== (int) $parent->tenant_id
            || ! in_array((int) $order->student_id, array_map('intval', $studentIds), true)
        ) {
            abort(404);
        }
    }
}
