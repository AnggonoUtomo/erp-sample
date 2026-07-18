<?php

use App\Modules\HR\HRReports\Http\Controllers\HRReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/reports')->name('hr.reports.')->group(function () {
    Route::get('/', [HRReportsController::class, 'index'])->name('index');
});
