<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        abort_unless(is_string($locale) && in_array($locale, config('i18n.supported'), true), 404);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
