<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalletTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->integer('tenant_id') ?: null;
        $studentId = $request->integer('student_id') ?: null;
        $transactionType = $request->string('transaction_type')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $transactions = WalletTransaction::query()
            ->with(['wallet', 'student', 'creator'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
            ->when($transactionType, fn ($query) => $query->where('transaction_type', $transactionType))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.wallet_transactions.index', [
            'title' => 'Extrato',
            'transactions' => $transactions,
            'tenants' => DB::table('tenants')->select(['id', 'name'])->orderBy('name')->get(),
            'tenantNames' => DB::table('tenants')->pluck('name', 'id'),
            'students' => Student::query()->select(['id', 'tenant_id', 'name'])->orderBy('name')->get(),
            'transactionTypes' => $this->transactionTypes(),
            'tenantId' => $tenantId,
            'studentId' => $studentId,
            'transactionType' => $transactionType,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function show(WalletTransaction $walletTransaction): View
    {
        $walletTransaction->load(['wallet', 'student', 'creator']);

        return view('pages.admin.wallet_transactions.show', [
            'title' => 'Detalhes da Transação',
            'transaction' => $walletTransaction,
            'tenantName' => DB::table('tenants')->where('id', $walletTransaction->tenant_id)->value('name'),
        ]);
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
