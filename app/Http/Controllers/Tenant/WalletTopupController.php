<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\WalletTopup;
use App\Services\WalletTopupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletTopupController extends Controller
{
    public function __construct(private readonly WalletTopupService $topups) {}

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $status = $request->string('status')->toString();
        $search = trim((string) $request->get('search'));

        $topups = WalletTopup::query()
            ->with(['student', 'parent'])
            ->where('tenant_id', $tenantId)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('code', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('parent', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("case when status = 'pending_review' then 0 when status = 'awaiting_payment' then 1 else 2 end")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.tenant.wallet_topups.index', [
            'title' => 'Recargas Pix',
            'topups' => $topups,
            'statuses' => WalletTopup::STATUSES,
            'status' => $status,
            'search' => $search,
            'pendingCount' => WalletTopup::query()
                ->where('tenant_id', $tenantId)
                ->where('status', WalletTopup::STATUS_PENDING_REVIEW)
                ->count(),
        ]);
    }

    public function show(Request $request, WalletTopup $walletTopup): View
    {
        $this->ensureBelongsToTenant($request, $walletTopup);
        $walletTopup->load(['student.school', 'parent', 'reviewer']);

        return view('pages.tenant.wallet_topups.show', [
            'title' => 'Recarga #'.$walletTopup->code,
            'topup' => $walletTopup,
            'statuses' => WalletTopup::STATUSES,
        ]);
    }

    public function approve(Request $request, WalletTopup $walletTopup): RedirectResponse
    {
        $this->ensureBelongsToTenant($request, $walletTopup);
        $this->topups->approve($walletTopup, $request->user());

        return redirect()
            ->route('tenant.wallet-topups.show', $walletTopup)
            ->with('success', 'Crédito liberado na carteira do aluno.');
    }

    public function reject(Request $request, WalletTopup $walletTopup): RedirectResponse
    {
        $this->ensureBelongsToTenant($request, $walletTopup);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Informe o motivo da recusa.',
        ]);

        $this->topups->reject($walletTopup, $request->user(), $validated['rejection_reason']);

        return redirect()
            ->route('tenant.wallet-topups.show', $walletTopup)
            ->with('success', 'Recarga recusada.');
    }

    private function ensureBelongsToTenant(Request $request, WalletTopup $topup): void
    {
        if ((int) $topup->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }
}
