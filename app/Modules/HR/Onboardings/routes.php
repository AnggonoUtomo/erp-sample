<?php

use App\Modules\HR\Onboardings\Http\Controllers\OnboardingsController;
use App\Modules\HR\Onboardings\Http\Controllers\OnboardingTemplatesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('hr/onboardings/templates')->name('hr.onboardings.templates.')->group(function () {
    Route::get('/', [OnboardingTemplatesController::class, 'index'])->name('index');
    Route::post('/', [OnboardingTemplatesController::class, 'store'])->name('store');
    Route::delete('{template}', [OnboardingTemplatesController::class, 'archive'])->name('archive');
    Route::patch('{template}/restore', [OnboardingTemplatesController::class, 'restore'])->withTrashed()->name('restore');
});

Route::middleware('auth')->prefix('hr/onboardings')->name('hr.onboardings.')->group(function () {
    Route::get('/', [OnboardingsController::class, 'index'])->name('index');
    Route::post('/', [OnboardingsController::class, 'store'])->name('store');
    Route::get('{onboarding}', [OnboardingsController::class, 'show'])->withTrashed()->name('show');
});
