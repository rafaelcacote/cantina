<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMenuItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'daily_menu_id',
        'product_id',
        'planned_quantity',
        'available_quantity',
        'price_override',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
