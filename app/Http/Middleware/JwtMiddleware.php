<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token has expired'], 401);
            } else if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token is invalid'], 401);
            } else {
                return response()->json(['error' => 'Token not found'], 401);
            }
        }
        return $next($request);
    }
}
