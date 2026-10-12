<?php

namespace App\Providers;

use App\Support\LocalizedRoutes;
use App\Support\PortfolioContact;
use App\Support\SupportedLocales;
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
            $locales = app(SupportedLocales::class);
            $view->with('profileLinks', app(PortfolioContact::class)->profileLinks());
            $view->with('localeSwitchUrls', app(LocalizedRoutes::class)->switchUrls(request()));
            $view->with('localeAlternates', app(LocalizedRoutes::class)->alternates(request()));
            $view->with('localeOptions', $locales->enabled());
            $view->with('defaultLocale', $locales->default());
        });
    }
}
