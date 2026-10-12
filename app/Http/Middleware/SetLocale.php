<?php

namespace App\Http\Middleware;

use App\Support\SupportedLocales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        abort_unless(app(SupportedLocales::class)->supports($locale), 404);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
