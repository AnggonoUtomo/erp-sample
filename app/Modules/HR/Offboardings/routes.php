<?php

use App\Modules\HR\Offboardings\Http\Controllers\OffboardingTemplatesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('hr/offboardings/templates')
    ->name('hr.offboardings.templates.')
    ->group(function () {
        Route::get('/', [OffboardingTemplatesController::class, 'index'])->name('index');
        Route::post('/', [OffboardingTemplatesController::class, 'store'])->name('store');
    });
