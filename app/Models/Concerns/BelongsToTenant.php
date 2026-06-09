<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (app()->runningInConsole() || ! Auth::check()) {
                return;
            }

            $user = Auth::user();

            if ($user->user_type !== 'super_admin' && empty($model->tenant_id)) {
                $model->tenant_id = $user->tenant_id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->runningInConsole() || ! Auth::check()) {
                return;
            }

            $user = Auth::user();

            if ($user->user_type !== 'super_admin' && $user->tenant_id) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $user->tenant_id);
            }
        });
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where($this->getTable().'.tenant_id', $tenantId);
    }
}
