<?php

use App\Modules\HR\JobLevels\Http\Controllers\JobLevelsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/job-levels')->name('hr.job-levels.')->group(function () {
    Route::get('/', [JobLevelsController::class, 'index'])->name('index');
    Route::post('/', [JobLevelsController::class, 'store'])->name('store');
    Route::put('{jobLevel}', [JobLevelsController::class, 'update'])->name('update');
    Route::delete('{jobLevel}', [JobLevelsController::class, 'destroy'])->name('destroy');
    Route::patch('{jobLevel}/restore', [JobLevelsController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{jobLevel}/force', [JobLevelsController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
