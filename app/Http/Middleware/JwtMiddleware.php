<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use App\Traits\ApiResponser;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    use ApiResponser;

    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return $this->errorResponse( 'El Token no es válido', 401 );
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return $this->errorResponse( 'El Token ha expirado', 401 );
            }else{
                return $this->errorResponse( 'Token de autorización no encontrado', 401 );
            }
        }
        return $next($request);
    }
}