<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesParentProfile;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $parent = ParentGuardian::forPortalUser($user);

        $links = $parent ? $this->linksFor($parent) : collect();
        $children = $links->map(fn ($link) => $this->presentChild($link))->values();
        $studentIds = $children->pluck('id')->filter()->all();

        $openOrders = $studentIds
            ? Order::query()
                ->where('tenant_id', (int) $user->tenant_id)
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
                ->count()
            : 0;

        $firstName = str($user->name)->explode(' ')->first() ?: $user->name;

        return Inertia::render('Parent/Dashboard', [
            'greeting' => $firstName,
            'children' => $children,
            'metrics' => [
                'children_count' => $children->count(),
                'total_balance' => (float) $children->sum('balance'),
                'open_orders' => $openOrders,
                'open_tab' => (float) $children->sum('tab_balance'),
            ],
        ]);
    }
}
