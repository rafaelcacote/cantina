<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Student;
use App\Models\StudentWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function credit(
        Student $student,
        float $amount,
        string $description,
        ?User $actor = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'O valor do crédito precisa ser maior que zero.',
            ]);
        }

        return DB::transaction(function () use ($student, $amount, $description, $actor, $referenceType, $referenceId) {
            $wallet = $this->ensureForStudent($student);
            $wallet = StudentWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $before = (float) $wallet->balance;
            $after = round($before + $amount, 2);
            $wallet->update(['balance' => $after]);

            return WalletTransaction::query()->create([
                'tenant_id' => $student->tenant_id,
                'wallet_id' => $wallet->id,
                'student_id' => $student->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'created_by' => $actor?->id,
            ]);
        });
    }

    public function ensureForStudent(Student $student): StudentWallet
    {
        return StudentWallet::query()->firstOrCreate(
            [
                'tenant_id' => $student->tenant_id,
                'student_id' => $student->id,
            ],
            [
                'balance' => 0,
                'credit_limit' => 0,
                'allow_negative_balance' => false,
            ],
        );
    }

    public function hasDebitedForOrder(Order $order): bool
    {
        return WalletTransaction::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('transaction_type', 'debit')
            ->exists();
    }

    public function debitForOrder(Order $order, ?User $actor = null): WalletTransaction
    {
        if ($this->hasDebitedForOrder($order)) {
            return WalletTransaction::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('reference_type', 'order')
                ->where('reference_id', $order->id)
                ->where('transaction_type', 'debit')
                ->firstOrFail();
        }

        if (! $order->student_id) {
            throw ValidationException::withMessages([
                'status' => 'Pedido com pagamento por carteira precisa de um aluno vinculado.',
            ]);
        }

        $amount = (float) $order->final_amount;
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'status' => 'Valor final do pedido inválido para débito na carteira.',
            ]);
        }

        return DB::transaction(function () use ($order, $actor, $amount) {
            $wallet = StudentWallet::query()
                ->where('tenant_id', $order->tenant_id)
                ->where('student_id', $order->student_id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'status' => 'Aluno não possui carteira cadastrada.',
                ]);
            }

            $before = (float) $wallet->balance;
            $after = round($before - $amount, 2);

            if ($after < 0 && ! $wallet->allow_negative_balance) {
                $creditLimit = (float) $wallet->credit_limit;
                if ($after < -$creditLimit) {
                    throw ValidationException::withMessages([
                        'status' => sprintf(
                            'Saldo insuficiente na carteira (saldo: R$ %s, pedido: R$ %s).',
                            number_format($before, 2, ',', '.'),
                            number_format($amount, 2, ',', '.')
                        ),
                    ]);
                }
            }

            $wallet->update(['balance' => $after]);

            return WalletTransaction::query()->create([
                'tenant_id' => $order->tenant_id,
                'wallet_id' => $wallet->id,
                'student_id' => $order->student_id,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => "Débito do pedido #{$order->id}",
                'created_by' => $actor?->id,
            ]);
        });
    }

    public function refundForOrder(Order $order, ?User $actor = null): void
    {
        if (! $this->hasDebitedForOrder($order)) {
            return;
        }

        $alreadyRefunded = WalletTransaction::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('reference_type', 'order_cancel')
            ->where('reference_id', $order->id)
            ->where('transaction_type', 'refund')
            ->exists();

        if ($alreadyRefunded) {
            return;
        }

        $debit = WalletTransaction::query()
            ->where('tenant_id', $order->tenant_id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('transaction_type', 'debit')
            ->firstOrFail();

        DB::transaction(function () use ($order, $actor, $debit) {
            $wallet = StudentWallet::query()
                ->whereKey($debit->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (float) $debit->amount;
            $before = (float) $wallet->balance;
            $after = round($before + $amount, 2);

            $wallet->update(['balance' => $after]);

            WalletTransaction::query()->create([
                'tenant_id' => $order->tenant_id,
                'wallet_id' => $wallet->id,
                'student_id' => $order->student_id,
                'transaction_type' => 'refund',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'order_cancel',
                'reference_id' => $order->id,
                'description' => "Estorno do pedido #{$order->id}",
                'created_by' => $actor?->id,
            ]);
        });
    }
}
