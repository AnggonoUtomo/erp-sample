<?php

use App\Modules\HR\EmploymentStatuses\Http\Controllers\EmploymentStatusesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employment-statuses')->name('hr.employment-statuses.')->group(function () {
    Route::get('/', [EmploymentStatusesController::class, 'index'])->name('index');
    Route::post('/', [EmploymentStatusesController::class, 'store'])->name('store');
    Route::put('{employmentStatus}', [EmploymentStatusesController::class, 'update'])->name('update');
    Route::delete('{employmentStatus}', [EmploymentStatusesController::class, 'destroy'])->name('destroy');
    Route::patch('{employmentStatus}/restore', [EmploymentStatusesController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{employmentStatus}/force', [EmploymentStatusesController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
