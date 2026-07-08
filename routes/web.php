<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReportController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return redirect()->route('login');
});

Route::domain('{subdomain}.clusterfiy.test')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Task routes
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // Task actions (status, comments, timer)
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.add-comment');
        Route::post('/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])->name('tasks.start-timer');
        Route::post('/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])->name('tasks.stop-timer');

        // Department routes (full resource)
        Route::resource('departments', DepartmentController::class);

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});

Route::middleware(['auth', 'is_super_admin'])->prefix('admin')->group(function () {
    Route::resource('companies', CompanyController::class)->except(['show']);
});

Route::post('/switch-company', [CompanySwitchController::class, 'switch'])->name('switch-company')->middleware('auth');
Route::post('/reset-company', [CompanySwitchController::class, 'reset'])->name('reset-company')->middleware('auth');
