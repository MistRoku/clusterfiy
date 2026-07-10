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

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Main domain - public entry
Route::get('/', function () {
    return redirect()->route('login');
});

// ─── Main domain dashboard (for Super Admin or auto‑redirect) ───
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = Auth::user();

    // Super Admin: go to company management
    if ($user->isSuperAdmin()) {
        return redirect()->route('companies.index');
    }

    // If user belongs to a company, redirect to that company's tenant dashboard
    if ($user->company) {
        return redirect()->route('tenant.dashboard', ['subdomain' => $user->company->subdomain]);
    }

    // Fallback: no company assigned
    abort(403, 'You are not assigned to any company.');
})->name('dashboard');

// ─── Global Super Admin routes (outside subdomain) ───
Route::middleware(['auth', 'is_super_admin'])->prefix('admin')->group(function () {
    Route::resource('companies', CompanyController::class)->except(['show']);
});

// ─── Company switching (for Super Admin) ───
Route::post('/switch-company', [CompanySwitchController::class, 'switch'])
    ->name('switch-company')
    ->middleware('auth');

Route::post('/reset-company', [CompanySwitchController::class, 'reset'])
    ->name('reset-company')
    ->middleware('auth');

// ─── Tenant (subdomain) routes ───
Route::domain('{subdomain}.clusterfiy.test')->group(function () {

    // All tenant routes require authentication
    Route::middleware(['auth'])->group(function () {

        // Tenant dashboard – name it differently to avoid collision
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('tenant.dashboard');

        // Task management
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // Task actions
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.add-comment');
        Route::post('/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])->name('tasks.start-timer');
        Route::post('/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])->name('tasks.stop-timer');

        // Departments
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
