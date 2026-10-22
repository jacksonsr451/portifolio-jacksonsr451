<?php

use App\Console\Commands\ImportPortfolioArticles;
use App\Console\Commands\ImportPortfolioProjects;
use App\Console\Commands\ProvisionPortfolioAdmin;
use App\Http\Middleware\InitializeLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([ImportPortfolioProjects::class, ProvisionPortfolioAdmin::class, ImportPortfolioArticles::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(InitializeLocale::class);
        $middleware->redirectGuestsTo(fn (Request $request): string => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
