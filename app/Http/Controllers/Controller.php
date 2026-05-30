<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="WowoClean API Documentation",
 *     description="API dokumentasi untuk WowoClean - Sistem Manajemen Kontainer Limbah",
 *     @OA\Contact(
 *         email="admin@wowoclean.com",
 *         name="WowoClean Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Server V1"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Masukkan token JWT yang didapat dari endpoint login"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoint untuk autentikasi pengguna"
 * )
 * @OA\Tag(
 *     name="Containers",
 *     description="Endpoint untuk manajemen kontainer limbah via Gateway"
 * )
 */
class Controller
{
    //
}
