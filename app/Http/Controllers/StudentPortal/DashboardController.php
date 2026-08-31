<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Student;
use App\Services\AppMenuService;
use App\Services\PinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PinService $pins,
        private readonly AppMenuService $menu,
    ) {}

    protected function portalRole(): string
    {
        return 'student';
    }

    protected function routeName(string $name): string
    {
        return 'student.'.$name;
    }

    protected function basePath(): string
    {
        return '/'.$this->portalRole();
    }

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
            'portalRole' => $this->portalRole(),
            'basePath' => $this->basePath(),
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

        return Inertia::render('Student/Menu', [
            'portalRole' => $this->portalRole(),
            'basePath' => $this->basePath(),
            'dateLabel' => now()->format('d/m/Y'),
            'menuTitle' => 'Produtos da cantina',
            'items' => $this->menu->catalogForStudent($student, (int) $request->user()->tenant_id),
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
            'portalRole' => $this->portalRole(),
            'basePath' => $this->basePath(),
            'orders' => $orders,
        ]);
    }

    public function account(Request $request): Response
    {
        $user = $request->user();
        $student = Student::forPortalUser($user);
        $isRequester = $this->portalRole() === 'requester';

        return Inertia::render('Student/Account', [
            'portalRole' => $this->portalRole(),
            'basePath' => $this->basePath(),
            'student' => [
                'name' => $student?->name ?: $user->name,
                'email' => $user->email,
                'school' => $student?->school?->name,
                'enrollment' => $isRequester ? null : $student?->enrollment_number,
                'grade' => $isRequester ? null : $student?->grade,
                'classroom' => $isRequester ? null : $student?->classroom,
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
            abort(403, 'Perfil de consumo não vinculado a este usuário.');
        }

        $validated = $request->validate([
            'personal_pin' => ['required', 'digits_between:4,8'],
            'personal_pin_confirmation' => ['required', 'same:personal_pin'],
        ]);

        $this->pins->assign($student, $validated['personal_pin']);

        return redirect()
            ->route($this->routeName('account'))
            ->with('success', 'PIN atualizado.');
    }
}
