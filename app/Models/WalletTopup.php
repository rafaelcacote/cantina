<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopup extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_AWAITING_PAYMENT => 'Aguardando pagamento',
        self::STATUS_PENDING_REVIEW => 'Aguardando conferência',
        self::STATUS_APPROVED => 'Creditado',
        self::STATUS_REJECTED => 'Recusado',
    ];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'parent_id',
        'code',
        'amount',
        'pix_key',
        'status',
        'receipt_path',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'wallet_transaction_id',
        'payment_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function canUploadReceipt(): bool
    {
        return in_array($this->status, [self::STATUS_AWAITING_PAYMENT, self::STATUS_PENDING_REVIEW], true);
    }

    public function canReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    public function receiptSrc(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        if (str_starts_with($this->receipt_path, 'http://') || str_starts_with($this->receipt_path, 'https://')) {
            return $this->receipt_path;
        }

        return '/storage/'.$this->receipt_path;
    }

    public function formattedAmount(): string
    {
        return 'R$ '.number_format((float) $this->amount, 2, ',', '.');
    }

    public function whatsappUrl(): string
    {
        $student = $this->student?->name ?? 'aluno';
        $text = "Recarga #{$this->code}\nAluno: {$student}\nValor: {$this->formattedAmount()}\nChave Pix: {$this->pix_key}\nJá paguei. Comprovante em anexo.";
        $phone = preg_replace('/\D+/', '', (string) $this->tenant?->phone);

        if ($phone !== '') {
            if (! str_starts_with($phone, '55')) {
                $phone = '55'.$phone;
            }

            return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
        }

        return 'https://wa.me/?text='.rawurlencode($text);
    }
}
