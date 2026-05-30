<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Checks if authenticated user has the required role.
     *
     * @param  string  $role  The required role (e.g., 'admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (!$user || $user->role !== $role) {
            return response()->json([
                'message' => 'Forbidden: Anda tidak memiliki akses untuk melakukan aksi ini.'
            ], 403);
        }

        return $next($request);
    }
}
