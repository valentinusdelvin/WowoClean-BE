<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ContainerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Gateway Routes (V1)
|--------------------------------------------------------------------------
|
| Semua endpoint diakses melalui prefix /api/v1/
| Contoh: /api/v1/login, /api/v1/gateway/containers
|
*/

Route::prefix('v1/gateway')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['jwt.auth'])->group(function () {

        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/containers', [ContainerController::class, 'index']);
        Route::get('/containers/search', [ContainerController::class, 'search']);
        Route::get('/containers/{id}/logs', [ContainerController::class, 'logs']);

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/containers', [ContainerController::class, 'store']);
            Route::patch('/containers/{id}', [ContainerController::class, 'update']);
            Route::delete('/containers/{id}', [ContainerController::class, 'destroy']);
            Route::post('/containers/{id}/logs', [ContainerController::class, 'storeLog']);
        });
    });
});