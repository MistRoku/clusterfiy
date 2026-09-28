<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('pages.home', [
        'title' => 'Clusterfiy: Multi-company team management',
        'metaDescription' => 'Clusterfiy is a multi-company team management app. One login, multiple companies, scoped tasks and reports.',
    ]);
})->name('home');

Route::get('/pricing', fn () => view('pages.pricing', ['title' => 'Clusterfiy pricing: Free and Team plans', 'metaDescription' => 'Compare Clusterfiy Free and Team plans. Free includes 1 company and 5 members. Team unlocks unlimited members and Excel exports.']))->name('pricing');
Route::get('/terms', fn () => view('pages.terms', ['title' => 'Clusterfiy Terms of Service', 'metaDescription' => 'Terms of Service for Clusterfiy team management software.']))->name('terms');
Route::get('/privacy', fn () => view('pages.privacy', ['title' => 'Clusterfiy Privacy Policy', 'metaDescription' => 'Privacy Policy for Clusterfiy. How company, task and account data is stored and used.']))->name('privacy');

Route::get('/sitemap.xml', function () {
    $urls = [route('home'), route('pricing'), route('terms'), route('privacy'), route('login')];
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) {
        $xml .= '<url><loc>' . e($u) . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/company/switch', [CompanySwitchController::class, 'switch'])->name('company.switch');
    Route::post('/company/reset', [CompanySwitchController::class, 'reset'])->name('company.reset');

    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments');
    Route::post('/tasks/{task}/timer/start', [TaskController::class, 'startTimer'])->name('tasks.timer.start');
    Route::post('/tasks/{task}/timer/stop', [TaskController::class, 'stopTimer'])->name('tasks.timer.stop');
    Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [TaskController::class, 'reject'])->name('tasks.reject');

    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('companies', CompanyController::class);

    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export')->middleware('plan:team');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

require __DIR__ . '/auth.php';
