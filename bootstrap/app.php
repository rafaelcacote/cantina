<?php

use App\Http\Middleware\EnsureOperator;
use App\Http\Middleware\EnsureParent;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTenantAdmin;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'tenant.context' => EnsureTenantContext::class,
            'super.admin' => EnsureSuperAdmin::class,
            'tenant.admin' => EnsureTenantAdmin::class,
            'operator' => EnsureOperator::class,
            'parent' => EnsureParent::class,
            'student' => EnsureStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
