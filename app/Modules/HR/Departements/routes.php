<?php

use App\Modules\HR\Departements\Http\Controllers\DepartementsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/departements')->name('hr.departements.')->group(function () {
    Route::get('/', [DepartementsController::class, 'index'])->name('index');
    Route::post('/', [DepartementsController::class, 'store'])->name('store');
    Route::put('{departement}', [DepartementsController::class, 'update'])->name('update');
    Route::delete('{departement}', [DepartementsController::class, 'destroy'])->name('destroy');
});
