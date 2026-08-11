<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    use ResolvesParentProfile;

    public function index(Request $request): Response
    {
        $parent = $this->parentFor($request);
        $studentIds = $this->linksFor($parent)->pluck('student_id')->filter()->all();

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
                    'student_name' => $order->student?->name,
                ])
                ->values()
                ->all()
            : [];

        return Inertia::render('Parent/Orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $parent = $this->parentFor($request);
        $studentIds = $this->linksFor($parent)->pluck('student_id')->filter()->all();

        if (
            (int) $order->tenant_id !== (int) $parent->tenant_id
            || ! in_array((int) $order->student_id, array_map('intval', $studentIds), true)
        ) {
            abort(404);
        }

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
}
