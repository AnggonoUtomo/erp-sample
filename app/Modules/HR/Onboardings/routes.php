<?php

use App\Modules\HR\Onboardings\Http\Controllers\OnboardingTemplatesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('hr/onboardings/templates')->name('hr.onboardings.templates.')->group(function () {
    Route::get('/', [OnboardingTemplatesController::class, 'index'])->name('index');
    Route::post('/', [OnboardingTemplatesController::class, 'store'])->name('store');
});
