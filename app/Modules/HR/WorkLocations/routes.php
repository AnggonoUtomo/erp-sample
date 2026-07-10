<?php

use App\Modules\HR\WorkLocations\Http\Controllers\WorkLocationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/work-locations')->name('hr.work-locations.')->group(function () {
    Route::get('/', [WorkLocationsController::class, 'index'])->name('index');
    Route::post('/', [WorkLocationsController::class, 'store'])->name('store');
    Route::put('{workLocation}', [WorkLocationsController::class, 'update'])->name('update');
    Route::delete('{workLocation}', [WorkLocationsController::class, 'destroy'])->name('destroy');
    Route::patch('{workLocation}/restore', [WorkLocationsController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{workLocation}/force', [WorkLocationsController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
