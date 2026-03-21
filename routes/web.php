<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeeUpdateController;
use App\Http\Controllers\EmployerDashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminUserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UpdateReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();

        if ($user->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('employer.dashboard');
    })->name('dashboard');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements/{announcement}/read', [AnnouncementController::class, 'markRead'])->name('announcements.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('privilege:view_activity_logs')->name('activity-logs.index');

    Route::middleware('role:employee')->group(function () {
        Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
        Route::post('/employee/updates', [EmployeeUpdateController::class, 'store'])->name('employee-updates.store');
        Route::put('/employee/updates/{employeeUpdate}', [EmployeeUpdateController::class, 'update'])->name('employee-updates.update');
    });

    Route::middleware('role:employer,admin')->group(function () {
        Route::get('/employer/dashboard', [EmployerDashboardController::class, 'index'])->name('employer.dashboard');
        Route::get('/exports/updates.csv', [ExportController::class, 'updatesCsv'])->middleware('privilege:export_reports')->name('exports.updates.csv');
        Route::get('/exports/updates.pdf', [ExportController::class, 'updatesPdf'])->middleware('privilege:export_reports')->name('exports.updates.pdf');
        Route::post('/update-reviews', [UpdateReviewController::class, 'store'])->middleware('privilege:review_updates')->name('update-reviews.store');
        Route::put('/update-reviews/{updateReview}', [UpdateReviewController::class, 'update'])->middleware('privilege:review_updates')->name('update-reviews.update');
        Route::get('/employees/{user}', [EmployeeProfileController::class, 'show'])->name('employees.show');

        Route::resource('teams', TeamController::class)->middleware('privilege:manage_teams')->except(['create', 'show', 'edit', 'destroy']);
        Route::post('/teams/assign', [TeamController::class, 'assign'])->middleware('privilege:manage_teams')->name('teams.assign');
        Route::post('/teams/remove-member', [TeamController::class, 'removeMember'])->middleware('privilege:manage_teams')->name('teams.remove-member');
        Route::post('/teams/set-primary', [TeamController::class, 'setPrimary'])->middleware('privilege:manage_teams')->name('teams.set-primary');
        Route::resource('announcements', AnnouncementController::class)->middleware('privilege:publish_announcements')->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/users', [SuperAdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [SuperAdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [SuperAdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [SuperAdminUserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/edit', [SuperAdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [SuperAdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [SuperAdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::put('/admin/users/{user}/access', [SuperAdminUserController::class, 'updateAccess'])->name('admin.users.update-access');
        Route::get('/admin/settings', [AdminSettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/settings/test-email', [AdminSettingsController::class, 'sendTestEmail'])->name('admin.settings.test-email');
        Route::post('/admin/settings/diagnostics', [AdminSettingsController::class, 'runDiagnostics'])->name('admin.settings.diagnostics');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
