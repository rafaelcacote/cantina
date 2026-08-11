<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Student;
use App\Models\StudentWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();

        $ordersQuery = Order::query()->where('tenant_id', $tenantId);
        $studentsQuery = Student::query()->where('tenant_id', $tenantId);
        $walletsQuery = StudentWallet::query()->where('tenant_id', $tenantId);

        if ($schoolId) {
            $ordersQuery->where('school_id', $schoolId);
            $studentsQuery->where('school_id', $schoolId);
            $walletsQuery->whereHas('student', fn ($q) => $q->where('school_id', $schoolId));
        }

        return view('pages.operator.dashboard', [
            'title' => 'Caixa',
            'schoolName' => $user->operatorProfile()?->school?->name,
            'metrics' => [
                'pending_orders' => (clone $ordersQuery)->where('status', 'pending')->count(),
                'today_orders' => (clone $ordersQuery)->whereDate('created_at', now()->toDateString())->count(),
                'students' => $studentsQuery->count(),
                'wallets_balance' => (float) $walletsQuery->sum('balance'),
            ],
        ]);
    }
}
