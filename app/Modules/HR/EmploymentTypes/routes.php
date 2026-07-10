<?php

use App\Modules\HR\EmploymentTypes\Http\Controllers\EmploymentTypesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employment-types')->name('hr.employment-types.')->group(function () {
    Route::get('/', [EmploymentTypesController::class, 'index'])->name('index');
    Route::post('/', [EmploymentTypesController::class, 'store'])->name('store');
    Route::put('{employmentType}', [EmploymentTypesController::class, 'update'])->name('update');
    Route::delete('{employmentType}', [EmploymentTypesController::class, 'destroy'])->name('destroy');
    Route::patch('{employmentType}/restore', [EmploymentTypesController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{employmentType}/force', [EmploymentTypesController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
