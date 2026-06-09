<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentParent extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'parent_id',
        'relationship_type',
        'is_primary',
        'financial_responsible',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'financial_responsible' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }
}
