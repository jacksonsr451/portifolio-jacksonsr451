<?php

namespace App\Providers;

use App\Support\LocalizedRoutes;
use App\Support\PortfolioContact;
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
        View::composer('layouts.app', function ($view): void {
            $view->with('profileLinks', app(PortfolioContact::class)->profileLinks());
            $view->with('localeSwitchUrls', app(LocalizedRoutes::class)->switchUrls(request()));
            $view->with('localeAlternates', app(LocalizedRoutes::class)->alternates(request()));
        });
    }
}
