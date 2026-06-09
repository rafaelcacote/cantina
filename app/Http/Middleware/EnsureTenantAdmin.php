<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->user_type !== 'tenant_admin') {
            abort(403, 'Acesso permitido apenas para tenant admin.');
        }

        if (! $user->tenant_id) {
            abort(403, 'Tenant admin sem tenant_id definido.');
        }

        return $next($request);
    }
}
