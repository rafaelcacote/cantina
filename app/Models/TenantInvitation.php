<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvitation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'token',
        'type',
        'expires_at',
        'max_uses',
        'used_count',
        'active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isUsable(): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function unusableReason(): string
    {
        if (! $this->active) {
            return 'Este convite foi desativado pela cantina.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Este convite expirou. Peça um novo link à cantina.';
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'Este convite já atingiu o limite de usos.';
        }

        return 'Este convite não está mais disponível.';
    }

    public function acceptUrl(): string
    {
        return route('invitations.accept', $this->token);
    }

    public function markUsed(): void
    {
        $this->increment('used_count');
        $this->refresh();

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            $this->update(['active' => false]);
        }
    }
}
