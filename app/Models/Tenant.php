<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'document',
        'email',
        'phone',
        'logo_url',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function parents(): HasMany
    {
        return $this->hasMany(ParentGuardian::class, 'tenant_id');
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function productSections(): HasMany
    {
        return $this->hasMany(ProductSection::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function dailyMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class);
    }

    public function dailyMenuItems(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function studentWallets(): HasMany
    {
        return $this->hasMany(StudentWallet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function studentTabs(): HasMany
    {
        return $this->hasMany(StudentTab::class);
    }

    public function tabEntries(): HasMany
    {
        return $this->hasMany(TabEntry::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function parentalControls(): HasMany
    {
        return $this->hasMany(ParentalControl::class);
    }

    public function allowedControlCategories(): HasMany
    {
        return $this->hasMany(ParentalControlAllowedCategory::class);
    }

    public function blockedControlProducts(): HasMany
    {
        return $this->hasMany(ParentalControlBlockedProduct::class);
    }

    public function preselectedOrders(): HasMany
    {
        return $this->hasMany(ParentalPreselectedOrder::class);
    }

    public function preselectedOrderItems(): HasMany
    {
        return $this->hasMany(ParentalPreselectedOrderItem::class);
    }

    public function purchaseAuthorizations(): HasMany
    {
        return $this->hasMany(PurchaseAuthorization::class);
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
