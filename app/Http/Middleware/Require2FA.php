<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Jika user telah login dan mengaktifkan 2FA, tetapi belum lolos verifikasi sesi
        if ($user && $user->google2fa_secret && !session('2fa_verified')) {
            // Mencegah loop tak berujung pada rute verifikasi itu sendiri
            if (!$request->is('2fa-verify')) {
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }
}
