<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInvitation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'parent_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
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

    public function isUsable(): bool
    {
        if ($this->used_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->student?->user_id) {
            return false;
        }

        return true;
    }

    public function acceptUrl(): string
    {
        return route('student-invitations.accept', $this->token);
    }

    public function unusableReason(): string
    {
        if ($this->student?->user_id || $this->used_at) {
            return 'Este convite já foi usado. Peça um novo link ao responsável.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Este convite expirou. Peça um novo link ao responsável.';
        }

        return 'Este convite não está mais disponível.';
    }
}
