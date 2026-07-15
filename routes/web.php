<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReportController;

require __DIR__ . '/auth.php';

// Override registration routes to disable them
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = Auth::user();
    if ($user->isSuperAdmin()) {
        return redirect()->route('companies.index');
    }
    if ($user->company) {
        return redirect()->route('tenant.dashboard', ['subdomain' => $user->company->subdomain]);
    }
    abort(403, 'You are not assigned to any company.');
})->name('dashboard');

Route::post('/switch-company', [CompanySwitchController::class, 'switch'])->name('switch-company')->middleware('auth');
Route::post('/reset-company', [CompanySwitchController::class, 'reset'])->name('reset-company')->middleware('auth');

Route::middleware(['auth', 'is_super_admin'])->prefix('admin')->group(function () {
    Route::resource('companies', CompanyController::class)->except(['show']);
});

Route::domain('{subdomain}.clusterfiy.test')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/submit-review', [TaskController::class, 'submitReview'])->name('tasks.submit-review');
        Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{task}/reject', [TaskController::class, 'reject'])->name('tasks.reject');
        Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.add-comment');
        Route::post('/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])->name('tasks.start-timer');
        Route::post('/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])->name('tasks.stop-timer');

        Route::resource('departments', DepartmentController::class);
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});