<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Logger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        error_log('========== HTTP REQUEST ==========');
        error_log('URL: ' . $request->fullUrl());
        error_log('METHOD: ' . $request->method());
        error_log('IP: ' . $request->ip());

        error_log('HEADERS: ' . json_encode(
            $request->headers->all(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ));

        error_log('BODY: ' . json_encode(
            $request->except([
                'password',
                'password_confirmation',
                'token',
            ]),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ));

        error_log('===================================');

        return $next($request);
    }
}
