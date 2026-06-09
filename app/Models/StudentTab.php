<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentTab extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'current_balance',
        'billing_cycle_type',
        'due_day',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'active' => 'boolean',
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

    public function entries(): HasMany
    {
        return $this->hasMany(TabEntry::class, 'student_tab_id');
    }
}
