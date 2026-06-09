<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'school_id',
        'student_id',
        'parent_id',
        'placed_by_user_id',
        'order_channel',
        'order_type',
        'status',
        'payment_mode',
        'total_amount',
        'discount_amount',
        'final_amount',
        'scheduled_for',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'scheduled_for' => 'datetime',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }

    public function placedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function purchaseAuthorizations(): HasMany
    {
        return $this->hasMany(PurchaseAuthorization::class);
    }
}
