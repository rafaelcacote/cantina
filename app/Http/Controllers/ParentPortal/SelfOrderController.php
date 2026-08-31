<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Services\AdultConsumerService;
use App\Services\AppMenuService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SelfOrderController extends Controller
{
    use ResolvesParentProfile;

    public function __construct(
        private readonly AdultConsumerService $adults,
        private readonly AppMenuService $menu,
    ) {}

    public function setup(Request $request): Response|RedirectResponse
    {
        $parent = $this->parentFor($request);
        $parent->loadMissing('selfStudent.school');

        if ($parent->selfStudent) {
            return redirect()->route('parent.self.menu');
        }

        $schools = $this->schoolOptions($parent);

        return Inertia::render('Parent/SelfSetup', [
            'schools' => $schools,
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $parent = $this->parentFor($request);

        $validated = $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $parent->tenant_id)
                    ->where('active', true)),
            ],
        ]);

        $this->adults->ensureForParent($parent, (int) $validated['school_id']);

        return redirect()
            ->route('parent.self.menu')
            ->with('success', 'Pronto! Agora você pode pedir para si mesmo.');
    }

    public function menu(Request $request): Response|RedirectResponse
    {
        $parent = $this->parentFor($request);
        $student = $this->resolveSelfStudent($parent, $request);

        if (! $student) {
            return redirect()->route('parent.self.setup');
        }

        return Inertia::render('Parent/Menu', [
            'dateLabel' => now()->format('d/m/Y'),
            'menuTitle' => 'Produtos da cantina',
            'items' => $this->menu->catalogForStudent($student, (int) $parent->tenant_id),
            'child' => $this->presentSelf($student),
            'checkoutHref' => route('parent.self.orders.create', absolute: false),
            'cartKey' => 'parent-cart-self-'.$student->id,
        ]);
    }

    public function checkout(Request $request): Response|RedirectResponse
    {
        $parent = $this->parentFor($request);
        $student = $this->resolveSelfStudent($parent, $request);

        if (! $student) {
            return redirect()->route('parent.self.setup');
        }

        $student->loadMissing('wallet');

        return Inertia::render('Parent/Checkout', [
            'walletBalance' => (float) ($student->wallet?->balance ?? 0),
            'paymentOptions' => $this->paymentOptions($student),
            'child' => $this->presentSelf($student),
            'menuHref' => route('parent.self.menu', absolute: false),
            'storeHref' => route('parent.self.orders.store', absolute: false),
            'cartKey' => 'parent-cart-self-'.$student->id,
        ]);
    }

    public function store(Request $request, OrderService $orderService): RedirectResponse
    {
        $parent = $this->parentFor($request);
        $student = $this->resolveSelfStudent($parent, $request);

        if (! $student) {
            return redirect()->route('parent.self.setup');
        }

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
            ->with('success', 'Pedido enviado para você.');
    }

    private function resolveSelfStudent($parent, Request $request): ?Student
    {
        $parent->loadMissing('selfStudent.school', 'selfStudent.wallet');

        return $parent->selfStudent;
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSelf(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => 'Você',
            'school' => $student->school?->name,
            'status' => $student->status,
            'balance' => (float) ($student->wallet?->balance ?? 0),
            'can_buy_on_tab' => (bool) $student->can_buy_on_tab,
            'can_order' => $student->status === 'active' && filled($student->school_id),
            'is_self' => true,
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function schoolOptions($parent): array
    {
        $fromChildren = $this->linksFor($parent)
            ->pluck('student.school_id')
            ->filter()
            ->unique()
            ->all();

        return School::query()
            ->where('tenant_id', $parent->tenant_id)
            ->where('active', true)
            ->when($fromChildren !== [], fn ($q) => $q->whereIn('id', $fromChildren))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])
            ->values()
            ->all();
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
                'hint' => 'Lançar na sua conta',
            ];
        }

        return $options;
    }
}
