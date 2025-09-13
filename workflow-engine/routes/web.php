<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('workflows', WorkflowController::class);
    Route::resource('requests', RequestController::class)->except(['edit', 'update', 'destroy']);

    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{requestStep}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{requestStep}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });
});
