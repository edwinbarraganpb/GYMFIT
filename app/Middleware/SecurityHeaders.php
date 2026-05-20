<?php

declare(strict_types=1);

namespace App\Middleware;

class SecurityHeaders
{
    /**
     * Apply OWASP-recommended security headers.
     */
    public static function apply(): void
    {
        header("Content-Security-Policy: default-src 'self'; "
             . "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
             . "style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; "
             . "img-src 'self' https://images.unsplash.com https://i.pravatar.cc data:; "
             . "connect-src 'self'; "
             . "frame-ancestors 'none'");
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }
}
