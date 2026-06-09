<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->user_type !== 'super_admin' && ! $user->tenant_id) {
            abort(403, 'Usuario sem contexto de tenant.');
        }

        return $next($request);
    }
}
