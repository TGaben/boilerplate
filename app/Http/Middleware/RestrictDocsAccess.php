<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDocsAccess
{
    /**
     * Handle an incoming request.
     *
     * Restrict access to documentation only in development environment.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow docs access only in development and testing environments
        if (!app()->environment(['local', 'development', 'testing'])) {
            abort(404, 'Documentation is only available in development environment.');
        }

        return $next($request);
    }
}
