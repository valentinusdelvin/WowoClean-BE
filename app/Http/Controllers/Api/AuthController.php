<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/v1/login',
        operationId: 'loginUser',
        tags: ['Authentication'],
        summary: 'Login pengguna',
        description: 'Autentikasi pengguna dengan email dan password, mengembalikan token JWT.',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Kredensial login',
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'admin@wowoclean.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Login berhasil.'
                        ),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Admin'),
                                new OA\Property(property: 'email', type: 'string', example: 'admin@wowoclean.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'admin'),
                            ]
                        ),
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
                        ),
                        new OA\Property(
                            property: 'token_type',
                            type: 'string',
                            example: 'Bearer'
                        ),
                        new OA\Property(
                            property: 'expires_in',
                            type: 'integer',
                            example: 86400
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Email atau password salah',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Unauthorized: Email atau password salah.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'The email field is required.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        )
                    ]
                )
            )
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Unauthorized: Email atau password salah.'
            ], 401);
        }

        $token = $this->generateToken($user);

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) env('JWT_TTL', 1440) * 60, // seconds
        ]);
    }

    #[OA\Get(
        path: '/api/v1/gateway/profile',
        operationId: 'getUserProfile',
        tags: ['Authentication'],
        summary: 'Ambil profil pengguna',
        description: 'Mengembalikan data profil pengguna yang sedang login.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'Admin'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    example: 'admin@wowoclean.com'
                                ),
                                new OA\Property(
                                    property: 'role',
                                    type: 'string',
                                    example: 'admin'
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token tidak valid atau tidak ada'
            )
        ]
    )]
    public function profile(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/v1/gateway/logout',
        operationId: 'logoutUser',
        tags: ['Authentication'],
        summary: 'Logout pengguna',
        description: 'Invalidasi token JWT saat ini. Token akan di-blacklist.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Logout berhasil. Token telah di-invalidasi.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token tidak valid atau tidak ada'
            )
        ]
    )] public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            // Blacklist the token using cache (TTL matches JWT expiry)
            $ttl = (int) env('JWT_TTL', 1440); // minutes
            Cache::put('jwt_blacklist_' . md5($token), true, now()->addMinutes($ttl));
        }

        return response()->json([
            'message' => 'Logout berhasil. Token telah di-invalidasi.'
        ]);
    }

    /**
     * Generate a JWT token for the given user.
     */
    private function generateToken(User $user): string
    {
        $secret = env('JWT_SECRET');
        $ttl = (int) env('JWT_TTL', 1440); // minutes

        $payload = [
            'iss' => config('app.url'),        // Issuer
            'sub' => $user->id,                 // Subject (user ID)
            'role' => $user->role,              // User role
            'iat' => time(),                    // Issued at
            'exp' => time() + ($ttl * 60),      // Expiration
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}
