<?php

use App\Modules\HR\Onboardings\Http\Controllers\OnboardingsController;
use App\Modules\HR\Onboardings\Http\Controllers\OnboardingTasksController;
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
    Route::patch('{onboarding}/activate', [OnboardingsController::class, 'activate'])->name('activate');
    Route::patch('{onboarding}/complete', [OnboardingsController::class, 'complete'])->name('complete');
    Route::patch('{onboarding}/cancel', [OnboardingsController::class, 'cancel'])->name('cancel');
    Route::patch('{onboarding}/tasks/{task}/assignment', [OnboardingTasksController::class, 'assignment'])->scopeBindings()->name('tasks.assignment');
    Route::patch('{onboarding}/tasks/{task}/start', [OnboardingTasksController::class, 'start'])->scopeBindings()->name('tasks.start');
    Route::patch('{onboarding}/tasks/{task}/complete', [OnboardingTasksController::class, 'complete'])->scopeBindings()->name('tasks.complete');
    Route::patch('{onboarding}/tasks/{task}/skip', [OnboardingTasksController::class, 'skip'])->scopeBindings()->name('tasks.skip');
    Route::patch('{onboarding}/tasks/{task}/reopen', [OnboardingTasksController::class, 'reopen'])->scopeBindings()->name('tasks.reopen');
    Route::get('{onboarding}', [OnboardingsController::class, 'show'])->withTrashed()->name('show');
});
