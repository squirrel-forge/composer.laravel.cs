<?php

namespace SquirrelForge\Laravel\CoreSupport\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Global response headers.
 */
class ResponseHeaders {

    /**
     * Handle an incoming request.
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = config('sqf-cs.headers');

        // Skip along if no headers are defined
        if (empty($headers)) return $next($request);

        // Get response object
        $response = $next($request);

        // Add headers to every response
        foreach ($headers as $name => $value) {
            if ($value instanceof Closure) $value = call_user_func($value, $request, $response);
            if (!empty($value)) $response->header($name, $value);
        }
        return $response;
    }
}
