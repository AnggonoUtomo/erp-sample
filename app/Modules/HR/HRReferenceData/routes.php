<?php

use App\Modules\HR\HRReferenceData\Http\Controllers\HRReferenceDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/hr-reference-data')->name('hr.hr-reference-data.')->group(function () {
    Route::get('/', [HRReferenceDataController::class, 'index'])->name('index');
    Route::post('/', [HRReferenceDataController::class, 'store'])->name('store');
    Route::post('categories', [HRReferenceDataController::class, 'storeCategory'])->name('categories.store');
    Route::put('categories/{referenceCategory}', [HRReferenceDataController::class, 'updateCategory'])->name('categories.update');
    Route::delete('categories/{referenceCategory}', [HRReferenceDataController::class, 'destroyCategory'])->name('categories.destroy');
    Route::put('{referenceData}', [HRReferenceDataController::class, 'update'])->name('update');
    Route::delete('{referenceData}', [HRReferenceDataController::class, 'destroy'])->name('destroy');
    Route::patch('{referenceData}/restore', [HRReferenceDataController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{referenceData}/force', [HRReferenceDataController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
