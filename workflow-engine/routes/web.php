<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Lightweight UI placeholder routes to avoid blank pages while backend APIs are built
    Route::view('/ui/requests/new', 'ui.request-new')->name('ui.requests.new');
    Route::view('/ui/approvals', 'ui.approvals')->name('ui.approvals');
    Route::view('/ui/workflows', 'ui.workflows')->name('ui.workflows');

    Route::resource('workflows', WorkflowController::class);
    Route::resource('requests', RequestController::class)->except(['edit', 'update', 'destroy']);

    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{requestStep}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{requestStep}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });
});

require __DIR__.'/auth.php';
