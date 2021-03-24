<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(auth('api')->check()) {
            if(is_null(auth('api')->user()->email_verified_at)){
                return response([
                    'success' => false,
                    'data' => [],
                    'message' => 'You did not verified email. Please verify email'
                ],
                    403);
            }
            return $next($request);
        }
        else {

            return response([
                'success' => false,
                'data' => [],
                'message' => 'Access denied. Please authenticate'
            ],
                403);
        }


    }
}
