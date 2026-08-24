<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRequester
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->user_type !== 'requester') {
            abort(403, 'Acesso permitido apenas para solicitantes.');
        }

        if (! $user->tenant_id) {
            abort(403, 'Solicitante sem tenant_id definido.');
        }

        return $next($request);
    }
}
