<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view): void {
            $tenant = auth()->user()?->tenant;

            $view->with([
                'brandHasTenant' => (bool) $tenant,
                'brandLogoSrc' => $tenant?->logoSrc(),
                'brandName' => $tenant?->name ?? config('app.name', 'Cantina'),
            ]);
        });
    }
}
