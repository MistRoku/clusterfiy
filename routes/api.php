<?php

use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\Api\CompanyController as ApiCompanyController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['throttle:api'])->group(function () {

    // Public auth endpoints
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Authenticated API routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::put('/auth/user', [AuthController::class, 'updateProfile']);

        // Dashboard
        Route::get('/dashboard', [ApiDashboardController::class, 'index']);

        // Companies
        Route::get('/companies', [ApiCompanyController::class, 'index']);
        Route::post('/companies/switch/{company}', [ApiCompanyController::class, 'switch']);

        // Tasks
        Route::apiResource('tasks', ApiTaskController::class);
        Route::patch('/tasks/{task}/status', [ApiTaskController::class, 'updateStatus']);
        Route::post('/tasks/{task}/comments', [ApiTaskController::class, 'storeComment']);
        Route::post('/tasks/{task}/attachments', [ApiTaskController::class, 'storeAttachment']);
        Route::get('/tasks/{task}/time-entries', [ApiTaskController::class, 'timeEntries']);
        Route::post('/tasks/{task}/time-entries', [ApiTaskController::class, 'storeTimeEntry']);
    });
});
