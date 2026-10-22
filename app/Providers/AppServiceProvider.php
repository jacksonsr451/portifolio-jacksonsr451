<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Project;
use App\Policies\ArticlePolicy;
use App\Policies\ProjectPolicy;
use App\Support\LocalizedRoutes;
use App\Support\PortfolioContact;
use App\Support\SupportedLocales;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
        RateLimiter::for('admin-login', fn ($request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
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
