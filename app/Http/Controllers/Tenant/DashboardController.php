<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ParentGuardian;
use App\Models\Product;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\StudentWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('pages.tenant.dashboard', [
            'title' => 'Dashboard do Tenant',
            'metrics' => [
                'total_schools' => School::query()->where('tenant_id', $tenantId)->count(),
                'total_students' => Student::query()->where('tenant_id', $tenantId)->count(),
                'total_parents' => ParentGuardian::query()->where('tenant_id', $tenantId)->count(),
                'total_products' => Product::query()->where('tenant_id', $tenantId)->count(),
                'total_orders' => Order::query()->where('tenant_id', $tenantId)->count(),
                'pending_orders' => Order::query()->where('tenant_id', $tenantId)->where('status', 'pending')->count(),
                'wallets_balance' => (float) StudentWallet::query()->where('tenant_id', $tenantId)->sum('balance'),
                'open_tab_balance' => (float) StudentTab::query()->where('tenant_id', $tenantId)->where('current_balance', '>', 0)->sum('current_balance'),
            ],
        ]);
    }
}
