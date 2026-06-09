<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'section_id',
        'category_id',
        'name',
        'description',
        'sku',
        'barcode',
        'product_type',
        'sale_type',
        'price',
        'cost_price',
        'image_url',
        'active',
        'visible_in_app',
        'allow_custom_request',
        'requires_preparation',
        'stock_controlled',
        'minimum_stock_alert',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'active' => 'boolean',
            'visible_in_app' => 'boolean',
            'allow_custom_request' => 'boolean',
            'requires_preparation' => 'boolean',
            'stock_controlled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function dailyMenuItems(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
