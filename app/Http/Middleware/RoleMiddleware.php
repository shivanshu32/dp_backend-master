<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,  ...$role)
    {
        if (!in_array($request->user()->role, $role)) {
            return response()->json(
                [
                    'code' => 403,
                    'message' => 'Unauthorized access',
                ], 403
            );
        }

        return $next($request);
    }

}
