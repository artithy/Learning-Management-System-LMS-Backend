<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $authUser = $request->attributes->get('auth_user');
        if (!$authUser || ($authUser['role'] ?? '') !== $role) {
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }
        return $next($request);
    }
}
