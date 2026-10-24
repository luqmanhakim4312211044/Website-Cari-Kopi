<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan header keamanan ke setiap respons halaman:
 * - X-Frame-Options: halaman tidak bisa dimasukkan ke iframe situs lain (anti-clickjacking).
 * - X-Content-Type-Options: browser tidak menebak tipe file.
 * - Referrer-Policy: URL lengkap tidak dikirim ke situs lain.
 * - Strict-Transport-Security: browser selalu memakai HTTPS (hanya di production).
 *
 * Content-Security-Policy sengaja tidak dipasang: Bootstrap, SweetAlert2, Google Fonts,
 * dan peta Google membutuhkan kebijakan yang rumit dan mudah merusak tampilan.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Sembunyikan versi PHP dari respons.
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        // Browser mengabaikan HSTS yang diterima lewat HTTP biasa, jadi aman dikirim di semua respons production.
        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
