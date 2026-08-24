<?php

namespace App\Models;

use App\Casts\EncryptedPin;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'school_id',
        'user_id',
        'profile_kind',
        'enrollment_number',
        'name',
        'birth_date',
        'grade',
        'classroom',
        'shift',
        'status',
        'photo_url',
        'personal_pin',
        'personal_pin_hash',
        'can_buy_on_credit',
        'can_buy_on_tab',
        'convenience_access',
        'snack_access',
    ];

    protected $hidden = [
        'personal_pin',
        'personal_pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'personal_pin' => EncryptedPin::class,
            'can_buy_on_credit' => 'boolean',
            'can_buy_on_tab' => 'boolean',
            'convenience_access' => 'boolean',
            'snack_access' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAdult(): bool
    {
        return ($this->profile_kind ?? 'student') === 'adult';
    }

    public static function forPortalUser(User $user): ?self
    {
        return static::query()
            ->with(['school', 'wallet'])
            ->where('tenant_id', (int) $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(StudentWallet::class);
    }

    public function tab(): HasOne
    {
        return $this->hasOne(StudentTab::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function tabEntries(): HasMany
    {
        return $this->hasMany(TabEntry::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function parentalControl(): HasOne
    {
        return $this->hasOne(ParentalControl::class);
    }

    public function preselectedOrders(): HasMany
    {
        return $this->hasMany(ParentalPreselectedOrder::class);
    }

    public function purchaseAuthorizations(): HasMany
    {
        return $this->hasMany(PurchaseAuthorization::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function photoSrc(): ?string
    {
        if (! $this->photo_url) {
            return null;
        }

        if (str_starts_with($this->photo_url, 'http://') || str_starts_with($this->photo_url, 'https://')) {
            return $this->photo_url;
        }

        return '/storage/'.$this->photo_url;
    }
}
