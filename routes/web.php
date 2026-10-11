<?php

use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/about', [PortfolioController::class, 'about'])->name('about');
Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects.index');
Route::get('/projects/{slug}', [PortfolioController::class, 'project'])->where('slug', '[a-z0-9-]+')->name('projects.show');
Route::get('/open-source', [PortfolioController::class, 'openSource'])->name('open-source');
Route::get('/writing', [PortfolioController::class, 'articles'])->name('articles.index');
Route::get('/writing/{slug}', [PortfolioController::class, 'article'])->where('slug', '[a-z0-9-]+')->name('articles.show');
Route::get('/contact', [PortfolioController::class, 'contact'])->name('contact');
Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PortfolioController::class, 'robots'])->name('robots');
