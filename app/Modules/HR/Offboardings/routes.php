<?php

use App\Modules\HR\Offboardings\Http\Controllers\OffboardingsController;
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
        Route::get('{offboarding}', [OffboardingsController::class, 'show'])
            ->withTrashed()
            ->name('show');
    });
