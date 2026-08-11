<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->user_type !== 'parent') {
            abort(403, 'Acesso permitido apenas para responsáveis.');
        }

        if (! $user->tenant_id) {
            abort(403, 'Responsável sem tenant_id definido.');
        }

        return $next($request);
    }
}
