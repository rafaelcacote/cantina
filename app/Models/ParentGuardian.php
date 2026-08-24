<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentGuardian extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'self_student_id',
        'name',
        'cpf',
        'phone',
        'email',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function selfStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'self_student_id');
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentParent::class, 'parent_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }

    public function preselectedOrders(): HasMany
    {
        return $this->hasMany(ParentalPreselectedOrder::class, 'parent_id');
    }

    public static function forPortalUser(User $user): ?self
    {
        return static::query()
            ->where('tenant_id', (int) $user->tenant_id)
            ->where('user_id', $user->id)
            ->first();
    }
}
