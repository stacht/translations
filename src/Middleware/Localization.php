<?php

namespace Stacht\Translations\Middleware;

use Closure;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Check header request and determine localizaton
        if ($request->hasHeader('x-locale')) {
            $locale = $request->header('x-locale');
        } else {
            // Otherwise get the browser preferred language
            $locale = $request->getPreferredLanguage(config('app.languages'));
        }

        // In the response headers set Content-Language
        $response->header('Content-Language', $locale);

        // set laravel localization
        app()->setLocale($locale);

        // continue request
        return $response;
    }
}
