<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ParentalControl;
use App\Models\Product;
use App\Models\Student;
use App\Services\PinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly PinService $pins) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $student = Student::forPortalUser($user);
        $displayName = $student?->name ?: $user->name;

        $recentOrders = [];

        if ($student) {
            $recentOrders = Order::query()
                ->where('student_id', $student->id)
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
                    'created_at' => $order->created_at?->format('d/m H:i'),
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Student/Dashboard', [
            'greeting' => $displayName,
            'student' => [
                'name' => $displayName,
                'school' => $student?->school?->name,
                'balance' => (float) ($student?->wallet?->balance ?? 0),
            ],
            'recentOrders' => $recentOrders,
        ]);
    }

    public function menu(Request $request): Response
    {
        $student = Student::forPortalUser($request->user());
        $tenantId = (int) $request->user()->tenant_id;
        $control = $this->enabledParentalControl($student);

        $items = Product::query()
            ->with(['section', 'category', 'stock'])
            ->where('tenant_id', $tenantId)
            ->where('active', true)
            ->where('visible_in_app', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (Product $product) => $this->studentCanSeeProduct($student, $product, $control))
            ->map(function (Product $product) {
                $stockControlled = (bool) $product->stock_controlled;
                $available = $stockControlled ? (int) ($product->stock?->quantity ?? 0) : null;

                return [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->imageSrc(),
                    'available' => $available,
                    'unlimited' => ! $stockControlled,
                    'can_order' => ! $stockControlled || $available > 0,
                    'price' => (float) $product->price,
                    'category_id' => $product->category_id,
                    'category' => $product->category?->name,
                ];
            })
            ->sortBy([
                fn (array $item) => mb_strtolower($item['category'] ?? 'zzz'),
                fn (array $item) => mb_strtolower($item['name']),
            ])
            ->values()
            ->all();

        return Inertia::render('Student/Menu', [
            'dateLabel' => now()->format('d/m/Y'),
            'menuTitle' => 'Produtos da cantina',
            'items' => $items,
        ]);
    }

    public function orders(Request $request): Response
    {
        $student = Student::forPortalUser($request->user());

        $orders = [];

        if ($student) {
            $orders = Order::query()
                ->with('items')
                ->where('student_id', $student->id)
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
                    'created_at' => $order->created_at?->format('d/m · H:i'),
                    'item_count' => $order->items->count(),
                    'preview' => $order->items
                        ->take(2)
                        ->pluck('item_name_snapshot')
                        ->filter()
                        ->implode(', '),
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Student/Orders', [
            'orders' => $orders,
        ]);
    }

    public function account(Request $request): Response
    {
        $user = $request->user();
        $student = Student::forPortalUser($user);

        return Inertia::render('Student/Account', [
            'student' => [
                'name' => $student?->name ?: $user->name,
                'email' => $user->email,
                'school' => $student?->school?->name,
                'enrollment' => $student?->enrollment_number,
                'grade' => $student?->grade,
                'classroom' => $student?->classroom,
                'balance' => (float) ($student?->wallet?->balance ?? 0),
                'can_buy_on_tab' => (bool) ($student?->can_buy_on_tab),
                'pin' => $student ? $this->pins->reveal($student) : null,
                'has_pin' => $student ? $this->pins->hasPin($student) : false,
            ],
        ]);
    }

    public function updatePin(Request $request): RedirectResponse
    {
        $student = Student::forPortalUser($request->user());

        if (! $student) {
            abort(403, 'Aluno não vinculado a este usuário.');
        }

        $validated = $request->validate([
            'personal_pin' => ['required', 'digits_between:4,8'],
            'personal_pin_confirmation' => ['required', 'same:personal_pin'],
        ]);

        $this->pins->assign($student, $validated['personal_pin']);

        return redirect()
            ->route('student.account')
            ->with('success', 'PIN atualizado.');
    }

    private function enabledParentalControl(?Student $student): ?ParentalControl
    {
        if (! $student) {
            return null;
        }

        return ParentalControl::query()
            ->with(['blockedProducts', 'allowedCategories'])
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->id)
            ->where('enabled', true)
            ->first();
    }

    private function studentCanSeeProduct(?Student $student, Product $product, ?ParentalControl $control): bool
    {
        $slug = $product->section?->slug;

        if ($control) {
            if ($slug === 'conveniencia' && ! $control->allow_convenience_access) {
                return false;
            }
            if ($slug === 'lanches' && ! $control->allow_snack_access) {
                return false;
            }
            if ($control->blockedProducts->contains('product_id', $product->id)) {
                return false;
            }
            if (in_array($control->control_mode, ['allowlist', 'mixed'], true)) {
                $allowedCategoryIds = $control->allowedCategories->pluck('category_id')->all();
                if ($allowedCategoryIds !== [] && ! in_array($product->category_id, $allowedCategoryIds, true)) {
                    return false;
                }
            }

            return true;
        }

        if ($slug === 'conveniencia' && $student && ! $student->convenience_access) {
            return false;
        }
        if ($slug === 'lanches' && $student && ! $student->snack_access) {
            return false;
        }

        return true;
    }
}
