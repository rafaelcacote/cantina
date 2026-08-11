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

        if (! in_array($user->user_type, ['tenant_admin', 'manager'], true)) {
            abort(403, 'Acesso permitido apenas para gestores do tenant.');
        }

        if (! $user->tenant_id) {
            abort(403, 'Usuário sem tenant_id definido.');
        }

        return $next($request);
    }
}
