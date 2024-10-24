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
        error_log(json_encode([
            'url' => $request->fullUrl(),
        ]));
        error_log(json_encode([
            'method' => $request->method(),
        ]));
        error_log(json_encode([
            'headers' => $request->headers->all(),
        ]));
        error_log(json_encode([
            'body' => $request->all(),
        ]));

        return $next($request);
    }
}
