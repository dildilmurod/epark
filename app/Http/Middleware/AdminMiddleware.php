<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth('api')->check()) {
            if (auth('api')->user()->role_id == 'admin') {
                return $next($request);
            }
            return response([
                'success' => false,
                'data' => [],
                'message' => 'Access denied. You are not Admin'
            ],
                403);
        }
        return response([
            'success' => false,
            'data' => [],
            'message' => 'Access denied. Please authenticate'
        ],
            403);


    }


}
