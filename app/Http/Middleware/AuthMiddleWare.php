<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json([
                'message' => 'Token is not provided'
            ]);
        }
        $token = substr($authHeader, 7);
        $secretKey = env('SECRET_KEY');
        try {
            $decode = JWT::decode($token, new Key($secretKey, 'HS256'));
            if (!isset($decode->exp) || $decode->exp < time()) {
                return response()->json([
                    'message' => 'Token is expired'
                ]);
            }
            $request->attributes->set('auth_user', [
                'id' => $decode->id,
                'name' => $decode->name
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Token is invalid'
            ]);
        }
        return $next($request);
    }
}
