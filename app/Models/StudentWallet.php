<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentWallet extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'balance',
        'credit_limit',
        'allow_negative_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'allow_negative_balance' => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }
}
