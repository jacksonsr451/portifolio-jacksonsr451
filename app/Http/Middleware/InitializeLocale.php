<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class InitializeLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('i18n.default');
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
