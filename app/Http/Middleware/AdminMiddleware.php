<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // ✅ FIX A09: Catat upaya akses admin tidak sah ke log keamanan
            \App\Services\SecurityLogger::unauthorizedAccess(
                Auth::user(), 
                $request->fullUrl(), 
                $request->ip()
            );

            abort(403, 'Akses ditolak. Hanya admin yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}
