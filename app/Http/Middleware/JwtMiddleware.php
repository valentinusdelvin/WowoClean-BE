<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     * Validates JWT token from Authorization header and sets the authenticated user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized: Token tidak ditemukan.'
            ], 401);
        }

        // Check if token has been blacklisted (logged out)
        if (Cache::has('jwt_blacklist_' . md5($token))) {
            return response()->json([
                'message' => 'Unauthorized: Token sudah tidak valid (logged out).'
            ], 401);
        }

        try {
            $secret = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            $user = User::find($decoded->sub);

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized: User tidak ditemukan.'
                ], 401);
            }

            // Set the authenticated user for the request
            auth()->login($user);
            $request->merge(['auth_user' => $user]);

        } catch (ExpiredException $e) {
            return response()->json([
                'message' => 'Unauthorized: Token sudah kedaluwarsa.'
            ], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'message' => 'Unauthorized: Token signature tidak valid.'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized: Token tidak valid.'
            ], 401);
        }

        return $next($request);
    }
}
