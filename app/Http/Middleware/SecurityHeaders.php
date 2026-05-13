<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ✅ Proteksi Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // ✅ Proteksi MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ✅ Proteksi Reflected XSS untuk Browser Lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ✅ Proteksi Man-in-the-Middle via HTTPS Enforcing
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // ✅ Kontrol Informasi Pengarah Link External
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ✅ Pembatasan Akses Hardware & Sensor Browser
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // ✅ Content Security Policy (Disesuaikan untuk mendukung CDN jQuery, Bootstrap, & Google reCAPTCHA)
        $cspRules = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "img-src 'self' data: https://www.gstatic.com/",
            "frame-src 'self' https://www.google.com/recaptcha/",
            "connect-src 'self' https://www.google.com/recaptcha/",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $cspRules));

        return $response;
    }
}
