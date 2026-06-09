<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $studentId = $request->integer('student_id') ?: null;
        $transactionType = $request->string('transaction_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $transactions = WalletTransaction::query()
            ->with(['wallet', 'student', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($transactionType, fn ($query) => $query->where('transaction_type', $transactionType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.tenant.wallet_transactions.index', [
            'title' => 'Extrato',
            'transactions' => $transactions,
            'students' => Student::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'transactionTypes' => $this->transactionTypes(),
            'studentId' => $studentId,
            'transactionType' => $transactionType,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(Request $request, WalletTransaction $walletTransaction): View
    {
        $this->ensureTransactionBelongsToTenant($request, $walletTransaction);
        $walletTransaction->load(['wallet', 'student', 'creator']);

        return view('pages.tenant.wallet_transactions.show', [
            'title' => 'Detalhes da Transação',
            'transaction' => $walletTransaction,
            'transactionTypes' => $this->transactionTypes(),
        ]);
    }

    private function ensureTransactionBelongsToTenant(Request $request, WalletTransaction $transaction): void
    {
        if ((int) $transaction->tenant_id !== (int) $request->user()->tenant_id) {
            abort(404);
        }
    }

    private function transactionTypes(): array
    {
        return [
            'credit' => 'Crédito',
            'debit' => 'Débito',
            'refund' => 'Estorno',
            'adjustment' => 'Ajuste',
        ];
    }
}
