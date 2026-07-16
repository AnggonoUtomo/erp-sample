<?php

use App\Modules\HR\EmployeeMovements\Http\Controllers\EmployeeMovementsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/employee-movements')->name('hr.employee-movements.')->group(function () {
    Route::get('/', [EmployeeMovementsController::class, 'index'])->name('index');
    Route::post('/', [EmployeeMovementsController::class, 'store'])->name('store');
    Route::post('/{employeeMovement}/approve', [EmployeeMovementsController::class, 'approve'])->name('approve');
    Route::post('/{employeeMovement}/apply', [EmployeeMovementsController::class, 'apply'])->name('apply');
    Route::post('/{employeeMovement}/cancel', [EmployeeMovementsController::class, 'cancel'])->name('cancel');
    Route::delete('/{employeeMovement}', [EmployeeMovementsController::class, 'destroy'])->name('destroy');
    Route::patch('/{employeeMovement}/restore', [EmployeeMovementsController::class, 'restore'])->name('restore');
});
