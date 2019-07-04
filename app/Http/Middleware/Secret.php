<?php

namespace App\Http\Middleware;

use App\Utils\Enumerators\RolesEnumerator;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Secret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (empty($request->bearerToken()) ||
            $request->bearerToken() !== env('CLIENT_SECRET')
        ) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    public function unauthorized()
    {
        return response(
            [
                'error' => [
                    'code'    => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Unauthorized.'
                ]
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
