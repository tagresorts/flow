<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/dashboard/{any}', function () {
        return view('dashboard');
    })->where('any', '.*');

    // (SPA handles subpages under /dashboard)

    Route::resource('workflows', WorkflowController::class);
    Route::resource('requests', RequestController::class)->except(['edit', 'update', 'destroy']);

    // Builder APIs
    Route::post('/builder/workflows/ensure-default', [WorkflowController::class, 'ensureDefault']);
    Route::post('/builder/workflows/{workflow}/visual', [WorkflowController::class, 'saveVisual']);
    Route::post('/builder/workflows/{workflow}/versions', [WorkflowController::class, 'createVersion']);
    Route::post('/builder/workflows/{workflow}/form-template', [WorkflowController::class, 'setFormTemplate']);

    Route::get('/builder/forms/templates', [FormTemplateController::class, 'index']);
    Route::post('/builder/forms/templates', [FormTemplateController::class, 'store']);

    // Dashboard APIs
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/requests', [DashboardController::class, 'list']);
    Route::get('/audit/logs', [AuditLogController::class, 'index']);

    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{requestStep}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{requestStep}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });
});

require __DIR__.'/auth.php';
