<?php

use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\PortfolioController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', [LegacyRedirectController::class, 'home'])->name('legacy.home');
Route::get('/about', [LegacyRedirectController::class, 'about'])->name('legacy.about');
Route::get('/projects', [LegacyRedirectController::class, 'projects'])->name('legacy.projects.index');
Route::get('/projects/{slug}', [LegacyRedirectController::class, 'project'])
    ->where('slug', '[a-z0-9-]+')->name('legacy.projects.show');
Route::get('/open-source', [LegacyRedirectController::class, 'openSource'])->name('legacy.open-source');
Route::get('/writing', [LegacyRedirectController::class, 'articles'])->name('legacy.articles.index');
Route::get('/writing/{slug}', [LegacyRedirectController::class, 'article'])
    ->where('slug', '[a-z0-9-]+')->name('legacy.articles.show');
Route::get('/contact', [LegacyRedirectController::class, 'contact'])->name('legacy.contact');

Route::get('/resume', [PortfolioController::class, 'downloadResume'])->name('resume.download');
Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortfolioController::class, 'robots'])->name('robots');

Route::prefix('{locale}')
    ->middleware(SetLocale::class)
    ->name('localized.')
    ->group(function (): void {
        Route::get('/', [PortfolioController::class, 'home'])->name('home');
        Route::get('/about', [PortfolioController::class, 'about'])->name('about');
        Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects.index');
        Route::get('/projects/{slug}', [PortfolioController::class, 'project'])->where('slug', '[a-z0-9-]+')->name('projects.show');
        Route::get('/open-source', [PortfolioController::class, 'openSource'])->name('open-source');
        Route::get('/writing', [PortfolioController::class, 'articles'])->name('articles.index');
        Route::get('/writing/{slug}', [PortfolioController::class, 'article'])->where('slug', '[a-z0-9-]+')->name('articles.show');
        Route::get('/contact', [PortfolioController::class, 'contact'])->name('contact');
    });
