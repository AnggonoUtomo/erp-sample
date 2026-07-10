<?php

use App\Modules\HR\Employees\Http\Controllers\EmployeesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employees')->name('hr.employees.')->group(function () {
    Route::get('/', [EmployeesController::class, 'index'])->name('index');
    Route::post('/', [EmployeesController::class, 'store'])->name('store');
    Route::match(['put', 'post'], '{employee}', [EmployeesController::class, 'update'])->name('update');
    Route::delete('{employee}', [EmployeesController::class, 'destroy'])->name('destroy');
    Route::patch('{employee}/restore', [EmployeesController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{employee}/force', [EmployeesController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
