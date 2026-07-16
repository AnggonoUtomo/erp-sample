<?php

use App\Modules\HR\Offboardings\Http\Controllers\OffboardingsController;
use App\Modules\HR\Offboardings\Http\Controllers\OffboardingTasksController;
use App\Modules\HR\Offboardings\Http\Controllers\OffboardingTemplatesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('hr/offboardings/templates')
    ->name('hr.offboardings.templates.')
    ->group(function () {
        Route::get('/', [OffboardingTemplatesController::class, 'index'])->name('index');
        Route::post('/', [OffboardingTemplatesController::class, 'store'])->name('store');
        Route::delete('{template}', [OffboardingTemplatesController::class, 'archive'])->name('archive');
        Route::patch('{template}/restore', [OffboardingTemplatesController::class, 'restore'])
            ->withTrashed()
            ->name('restore');
    });

Route::middleware('auth')
    ->prefix('hr/offboardings')
    ->name('hr.offboardings.')
    ->group(function () {
        Route::get('/', [OffboardingsController::class, 'index'])->name('index');
        Route::post('/', [OffboardingsController::class, 'store'])->name('store');
        Route::patch('{offboarding}/activate', [OffboardingsController::class, 'activate'])
            ->withTrashed()
            ->name('activate');
        Route::patch('{offboarding}/mark-ready', [OffboardingsController::class, 'markReady'])
            ->withTrashed()
            ->name('mark-ready');
        Route::patch('{offboarding}/cancel', [OffboardingsController::class, 'cancel'])
            ->withTrashed()
            ->name('cancel');
        Route::patch('{offboarding}/tasks/{task}/assignment', [OffboardingTasksController::class, 'assignment'])
            ->withTrashed()
            ->scopeBindings()
            ->name('tasks.assignment');
        Route::patch('{offboarding}/tasks/{task}/start', [OffboardingTasksController::class, 'start'])
            ->withTrashed()
            ->scopeBindings()
            ->name('tasks.start');
        Route::patch('{offboarding}/tasks/{task}/complete', [OffboardingTasksController::class, 'complete'])
            ->withTrashed()
            ->scopeBindings()
            ->name('tasks.complete');
        Route::patch('{offboarding}/tasks/{task}/skip', [OffboardingTasksController::class, 'skip'])
            ->withTrashed()
            ->scopeBindings()
            ->name('tasks.skip');
        Route::patch('{offboarding}/tasks/{task}/reopen', [OffboardingTasksController::class, 'reopen'])
            ->withTrashed()
            ->scopeBindings()
            ->name('tasks.reopen');
        Route::get('{offboarding}', [OffboardingsController::class, 'show'])
            ->withTrashed()
            ->name('show');
    });
