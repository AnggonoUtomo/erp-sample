<?php

use App\Modules\HR\Positions\Http\Controllers\PositionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/positions')->name('hr.positions.')->group(function () {
    Route::get('/', [PositionsController::class, 'index'])->name('index');
    Route::post('/', [PositionsController::class, 'store'])->name('store');
    Route::put('{position}', [PositionsController::class, 'update'])->name('update');
    Route::delete('{position}', [PositionsController::class, 'destroy'])->name('destroy');
});
