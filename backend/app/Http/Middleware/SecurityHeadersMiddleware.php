<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and enforce security headers and HTTPS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Mandatory HTTPS Redirection (Production or FORCE_HTTPS=true)
        $forceHttps = app()->environment('production') || env('FORCE_HTTPS', false);
        if ($forceHttps && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // 2. Set Core Security Headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 3. HSTS (HTTP Strict Transport Security)
        if ($forceHttps || $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // 4. Content-Security-Policy (Permits local assets, TipTap ESM CDN, fonts, and inline styles)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://esm.sh https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com data:; " .
               "img-src 'self' data: blob: https:; " .
               "connect-src 'self' https:; " .
               "frame-ancestors 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
