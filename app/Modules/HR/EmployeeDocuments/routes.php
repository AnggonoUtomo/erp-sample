<?php

use App\Modules\HR\EmployeeDocuments\Http\Controllers\EmployeeDocumentsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employee-documents')->name('hr.employee-documents.')->group(function () {
    Route::get('/', [EmployeeDocumentsController::class, 'index'])->name('index');
    Route::post('/', [EmployeeDocumentsController::class, 'store'])->name('store');
});
