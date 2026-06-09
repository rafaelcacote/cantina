<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'document',
        'phone',
        'email',
        'address',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function dailyMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
