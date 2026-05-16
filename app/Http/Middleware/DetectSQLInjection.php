<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;

class DetectSQLInjection
{
    private $patterns = [
        '/(\bunion\b.*\bselect\b)/i',
        '/(\bselect\b.*\bfrom\b)/i',
        '/(\binsert\b.*\binto\b)/i',
        '/(\bdelete\b.*\bfrom\b)/i',
        '/(\bdrop\b.*\btable\b)/i',
        '/(\'|\")(\s)*(or|and)(\s)+(\'|\"|\d)/i',
        '/(;|\-\-|\/\*)/i',
    ];

    public function handle($request, Closure $next)
    {
        $inputs = $request->all();
        $queryString = $request->getQueryString();

        $allInput = json_encode($inputs) . ' ' . $queryString;

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                SecurityLogger::suspiciousInput(
                    'SQL_INJECTION_ATTEMPT',
                    $allInput,
                    $request->ip()
                );
                break;
            }
        }

        return $next($request);
    }
}
