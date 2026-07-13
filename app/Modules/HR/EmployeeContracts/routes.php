<?php

use App\Modules\HR\EmployeeContracts\Http\Controllers\EmployeeContractsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('hr/employee-contracts')->name('hr.employee-contracts.')->group(function () {
    Route::get('/', [EmployeeContractsController::class, 'index'])->name('index');
    Route::post('/', [EmployeeContractsController::class, 'store'])->name('store');
    Route::post('{employeeContract}/activate', [EmployeeContractsController::class, 'activate'])->name('activate');
    Route::post('{employeeContract}/terminate', [EmployeeContractsController::class, 'terminate'])->name('terminate');
    Route::post('{employeeContract}/cancel', [EmployeeContractsController::class, 'cancel'])->name('cancel');
    Route::post('{employeeContract}/supersede', [EmployeeContractsController::class, 'supersede'])->name('supersede');
});
