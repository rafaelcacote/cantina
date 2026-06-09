<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentalPreselectedOrder extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'school_id',
        'parent_id',
        'student_id',
        'order_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ParentalPreselectedOrderItem::class);
    }
}
