<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentalControl extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'enabled',
        'control_mode',
        'daily_spending_limit',
        'weekly_spending_limit',
        'allow_tab_usage',
        'allow_wallet_usage',
        'allow_convenience_access',
        'allow_snack_access',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'daily_spending_limit' => 'decimal:2',
            'weekly_spending_limit' => 'decimal:2',
            'allow_tab_usage' => 'boolean',
            'allow_wallet_usage' => 'boolean',
            'allow_convenience_access' => 'boolean',
            'allow_snack_access' => 'boolean',
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

    public function allowedCategories(): HasMany
    {
        return $this->hasMany(ParentalControlAllowedCategory::class);
    }

    public function blockedProducts(): HasMany
    {
        return $this->hasMany(ParentalControlBlockedProduct::class);
    }
}
