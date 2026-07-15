<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'update']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('tasks', TaskController::class);
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment']);
        Route::post('/tasks/{task}/start-timer', [TaskController::class, 'startTimer']);
        Route::post('/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer']);

        Route::get('/companies', [CompanyController::class, 'index']);
        Route::post('/companies/switch', [CompanyController::class, 'switch']);
        Route::get('/company', [CompanyController::class, 'current']);
    });
});