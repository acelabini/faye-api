<?php

namespace App\Http\Middleware;

use App\Utils\Enumerators\RolesEnumerator;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Admin
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
        if (empty($request->user())) {
            return $this->notFoundResponse();
        }

        if ((int)$request->user()->role_id !== RolesEnumerator::ADMIN) {
            return $this->notFoundResponse();
        }

        return $next($request);
    }

    private function notFoundResponse()
    {
        return response(
            [
                'error' => [
                    'code'    => Response::HTTP_NOT_FOUND,
                    'message' => '404 Not found.'
                ]
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
