<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\StudentWallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $schoolId = $user->scopedSchoolId();
        $search = trim((string) $request->get('search'));

        $wallets = StudentWallet::query()
            ->with('student.school')
            ->where('tenant_id', $tenantId)
            ->when($schoolId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('school_id', $schoolId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.operator.wallets.index', [
            'title' => 'Carteiras',
            'wallets' => $wallets,
            'search' => $search,
        ]);
    }

    public function show(Request $request, StudentWallet $wallet): View
    {
        $user = $request->user();
        if ((int) $wallet->tenant_id !== (int) $user->tenant_id) {
            abort(404);
        }

        $wallet->load(['student.school', 'transactions' => fn ($q) => $q->latest()->limit(20)]);

        $schoolId = $user->scopedSchoolId();
        if ($schoolId && (int) $wallet->student?->school_id !== $schoolId) {
            abort(404);
        }

        return view('pages.operator.wallets.show', [
            'title' => 'Carteira',
            'wallet' => $wallet,
        ]);
    }
}
