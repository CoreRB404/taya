<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\TrackingController::class, 'landing'])->name('tracking.landing');
Route::get('/track', [\App\Http\Controllers\TrackingController::class, 'lookup'])->middleware('throttle:30,1')->name('tracking.lookup');
Route::get('/track/{code}', [\App\Http\Controllers\TrackingController::class, 'show'])->where('code', 'TAYA-[A-Z0-9]{6,12}')->middleware('throttle:30,1')->name('tracking.show');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,staff,lawyer,auditor,authorized_user')->group(function () {
        Route::resource('detainees', \App\Http\Controllers\DetaineeController::class);
        Route::post('detainees/{detainee}/release', [\App\Http\Controllers\DetaineeController::class, 'release'])->name('detainees.release');
        Route::post('phases/{phase}/complete', [\App\Http\Controllers\PhaseController::class, 'complete'])->name('phases.complete');
        Route::post('phases/{phase}/flag', [\App\Http\Controllers\PhaseController::class, 'flag'])->name('phases.flag');
        Route::resource('detainees.documents', \App\Http\Controllers\DocumentController::class)->only(['index', 'store', 'destroy', 'show']);
        Route::resource('detainees.legal-actions', \App\Http\Controllers\LegalActionController::class)->only(['index', 'store']);
    });

    Route::middleware('role:admin,staff,lawyer,auditor,authorized_user')->group(function () {
        Route::resource('alerts', \App\Http\Controllers\AlertController::class)->only(['index', 'show']);
        Route::post('alerts/{alert}/assign', [\App\Http\Controllers\AlertController::class, 'assign'])->name('alerts.assign');
        Route::post('alerts/{alert}/resolve', [\App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('alerts/{alert}/override', [\App\Http\Controllers\AlertController::class, 'adminOverride'])->name('alerts.override');
        
        // Admin user management
        Route::get('admin/users', [\App\Http\Controllers\AdminController::class, 'usersIndex'])->name('admin.users.index');
        Route::post('admin/users', [\App\Http\Controllers\AdminController::class, 'usersStore'])->name('admin.users.store');
        Route::put('admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'usersUpdate'])->name('admin.users.update');
        Route::delete('admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'usersDestroy'])->name('admin.users.destroy');
        Route::post('admin/users/bulk-reset-passwords', [\App\Http\Controllers\AdminController::class, 'usersBulkResetPasswords'])->name('admin.users.bulk-reset-passwords');
        Route::post('admin/users/{user}/reset-password', [\App\Http\Controllers\AdminController::class, 'usersResetPassword'])->name('admin.users.reset-password');
        Route::post('admin/users/{user}/change-password', [\App\Http\Controllers\AdminController::class, 'usersChangePassword'])->name('admin.users.change-password');
        
        // Admin facility management
        Route::get('admin/facilities', [\App\Http\Controllers\AdminController::class, 'facilitiesIndex'])->name('admin.facilities.index');
        Route::post('admin/facilities', [\App\Http\Controllers\AdminController::class, 'facilitiesStore'])->name('admin.facilities.store');
        Route::put('admin/facilities/{facility}', [\App\Http\Controllers\AdminController::class, 'facilitiesUpdate'])->name('admin.facilities.update');
        Route::delete('admin/facilities/{facility}', [\App\Http\Controllers\AdminController::class, 'facilitiesDestroy'])->name('admin.facilities.destroy');
        
        // Admin penalty references
        Route::get('admin/penalties', [\App\Http\Controllers\AdminController::class, 'penaltiesIndex'])->name('admin.penalties.index');
        Route::post('admin/penalties', [\App\Http\Controllers\AdminController::class, 'penaltiesStore'])->name('admin.penalties.store');
        Route::put('admin/penalties/{penalty}', [\App\Http\Controllers\AdminController::class, 'penaltiesUpdate'])->name('admin.penalties.update');
        Route::delete('admin/penalties/{penalty}', [\App\Http\Controllers\AdminController::class, 'penaltiesDestroy'])->name('admin.penalties.destroy');
        
    });

    Route::middleware('role:admin,auditor')->group(function () {
        Route::get('admin/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    });

    Route::middleware('role:admin,staff,lawyer,auditor,authorized_user')->group(function () {
        Route::get('reports/facility/{facility}', [\App\Http\Controllers\ReportController::class, 'facilityReport'])->name('reports.facility');
        Route::get('reports/alert/{alert}', [\App\Http\Controllers\ReportController::class, 'caseAlert'])->name('reports.alert');
        Route::get('reports/detainee/{detainee}', [\App\Http\Controllers\ReportController::class, 'detaineeProfile'])->name('reports.detainee');
        Route::get('reports/analytics', [\App\Http\Controllers\ReportController::class, 'policyAnalytics'])->name('reports.analytics');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
