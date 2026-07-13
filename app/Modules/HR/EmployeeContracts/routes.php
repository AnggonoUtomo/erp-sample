<?php

use App\Modules\HR\EmployeeContracts\Http\Controllers\EmployeeContractsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('hr/employee-contracts')->name('hr.employee-contracts.')->group(function () {
    Route::get('/', [EmployeeContractsController::class, 'index'])->name('index');
    Route::post('/', [EmployeeContractsController::class, 'store'])->name('store');
    Route::post('{employeeContract}/activate', [EmployeeContractsController::class, 'activate'])->name('activate');
});
