<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends `Cache-Control: no-store` on auth pages so the browser cannot serve
 * them from its back/forward cache.
 *
 * Why: Auth::login() (called from the registration controller) regenerates
 * the session's CSRF token. The browser still has the original /register
 * form in BFCache; pressing Back shows that cached form, and submitting
 * anything from it hits 419 Page Expired because the token has rotated.
 * `no-store` forces a fresh server roundtrip on Back, which the `guest`
 * middleware then redirects to /verify-email for already-authenticated
 * users instead of rendering a doomed-to-419 cached form.
 */
class NoBackCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
