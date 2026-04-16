<?php

use App\Http\Controllers\ContainerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/containers', [ContainerController::class, 'index']);
Route::post('/containers', [ContainerController::class, 'store']);
Route::patch('/containers/{id}', [ContainerController::class, 'update']);
Route::delete('/containers/{id}', [ContainerController::class, 'destroy']);
