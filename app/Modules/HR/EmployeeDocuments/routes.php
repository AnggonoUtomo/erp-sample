<?php

use App\Modules\HR\EmployeeDocuments\Http\Controllers\EmployeeDocumentsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employee-documents')->name('hr.employee-documents.')->group(function () {
    Route::get('/', [EmployeeDocumentsController::class, 'index'])->name('index');
    Route::post('/', [EmployeeDocumentsController::class, 'store'])->name('store');
    Route::post('{employeeDocument}/verify', [EmployeeDocumentsController::class, 'verify'])->name('verify');
    Route::post('{employeeDocument}/reject', [EmployeeDocumentsController::class, 'reject'])->name('reject');
    Route::post('{employeeDocument}/resubmit', [EmployeeDocumentsController::class, 'resubmit'])->name('resubmit');
    Route::delete('{employeeDocument}', [EmployeeDocumentsController::class, 'destroy'])->name('destroy');
    Route::patch('{employeeDocument}/restore', [EmployeeDocumentsController::class, 'restore'])->withTrashed()->name('restore');
    Route::post('{employeeDocument}/attachment', [EmployeeDocumentsController::class, 'attach'])->name('attachment.store');
    Route::delete('{employeeDocument}/attachment', [EmployeeDocumentsController::class, 'detach'])->name('attachment.destroy');
});
