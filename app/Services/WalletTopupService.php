<?php

namespace App\Services;

use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\WalletTopup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletTopupService
{
    public function __construct(private readonly WalletService $walletService) {}

    public function create(ParentGuardian $parent, Student $student, float $amount): WalletTopup
    {
        if ($amount < 1) {
            throw ValidationException::withMessages([
                'amount' => 'Informe um valor de pelo menos R$ 1,00.',
            ]);
        }

        $parent->loadMissing('tenant');
        $pixKey = trim((string) $parent->tenant?->pix);

        if ($pixKey === '') {
            throw ValidationException::withMessages([
                'amount' => 'A cantina ainda não cadastrou a chave Pix.',
            ]);
        }

        return WalletTopup::query()->create([
            'tenant_id' => $parent->tenant_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'code' => $this->uniqueCode((int) $parent->tenant_id),
            'amount' => round($amount, 2),
            'pix_key' => $pixKey,
            'status' => WalletTopup::STATUS_AWAITING_PAYMENT,
        ]);
    }

    public function attachReceipt(WalletTopup $topup, UploadedFile $file): WalletTopup
    {
        if (! $topup->canUploadReceipt()) {
            throw ValidationException::withMessages([
                'receipt' => 'Esta recarga não aceita mais comprovante.',
            ]);
        }

        $path = $file->store("wallet-topups/{$topup->tenant_id}", 'public');

        if ($topup->receipt_path) {
            Storage::disk('public')->delete($topup->receipt_path);
        }

        $topup->update([
            'receipt_path' => $path,
            'status' => WalletTopup::STATUS_PENDING_REVIEW,
        ]);

        return $topup->fresh();
    }

    public function approve(WalletTopup $topup, User $reviewer): WalletTopup
    {
        if (! $topup->canReview()) {
            throw ValidationException::withMessages([
                'status' => 'Só é possível creditar recargas aguardando conferência.',
            ]);
        }

        return DB::transaction(function () use ($topup, $reviewer) {
            $topup = WalletTopup::query()->whereKey($topup->id)->lockForUpdate()->firstOrFail();

            if (! $topup->canReview()) {
                throw ValidationException::withMessages([
                    'status' => 'Esta recarga já foi analisada.',
                ]);
            }

            $student = $topup->student()->firstOrFail();
            $transaction = $this->walletService->credit(
                $student,
                (float) $topup->amount,
                "Recarga Pix #{$topup->code}",
                $reviewer,
                'wallet_topup',
                $topup->id,
            );

            $payment = Payment::query()->create([
                'tenant_id' => $topup->tenant_id,
                'student_id' => $topup->student_id,
                'parent_id' => $topup->parent_id,
                'amount' => $topup->amount,
                'payment_method' => 'pix',
                'reference' => $topup->code,
                'status' => 'completed',
                'paid_at' => now(),
                'created_by' => $reviewer->id,
            ]);

            $topup->update([
                'status' => WalletTopup::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'wallet_transaction_id' => $transaction->id,
                'payment_id' => $payment->id,
                'rejection_reason' => null,
            ]);

            return $topup->fresh();
        });
    }

    public function reject(WalletTopup $topup, User $reviewer, string $reason): WalletTopup
    {
        if (! $topup->canReview()) {
            throw ValidationException::withMessages([
                'status' => 'Só é possível recusar recargas aguardando conferência.',
            ]);
        }

        $topup->update([
            'status' => WalletTopup::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $topup->fresh();
    }

    private function uniqueCode(int $tenantId): string
    {
        do {
            $code = strtoupper(Str::random(4));
        } while (
            WalletTopup::query()
                ->where('tenant_id', $tenantId)
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }
}
