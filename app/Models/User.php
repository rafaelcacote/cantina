<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'cpf',
        'user_type',
        'active',
        'last_login_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdInvitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class, 'created_by');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function parentProfiles(): HasMany
    {
        return $this->hasMany(ParentGuardian::class, 'user_id');
    }

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    public function placedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'placed_by_user_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'created_by');
    }

    public function tabEntries(): HasMany
    {
        return $this->hasMany(TabEntry::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function purchaseAuthorizations(): HasMany
    {
        return $this->hasMany(PurchaseAuthorization::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
