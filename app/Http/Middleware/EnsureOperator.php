<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->user_type !== 'operator') {
            abort(403, 'Acesso permitido apenas para operadores.');
        }

        if (! $user->tenant_id) {
            abort(403, 'Operador sem tenant_id definido.');
        }

        return $next($request);
    }
}
