<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the locale from session, fallback to default config locale
        $locale = Session::get(
            config('languages.session_key', 'locale'),
            config('languages.default', config('app.locale', 'hu')),
        );

        // Ensure the locale is supported
        $availableLanguages = array_keys(config('languages.available', []));
        if (! in_array($locale, $availableLanguages, true)) {
            $locale = config('languages.default', config('app.locale', 'hu'));
        }

        // Set the application locale
        App::setLocale($locale);

        return $next($request);
    }
}
