<?php

use App\Modules\HR\OrganizationStructures\Http\Controllers\OrganizationStructuresController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('hr/organization-structures')->name('hr.organization-structures.')->group(function () {
    Route::get('/', [OrganizationStructuresController::class, 'index'])->name('index');
    Route::post('/', [OrganizationStructuresController::class, 'store'])->name('store');
    Route::put('{organizationStructure}', [OrganizationStructuresController::class, 'update'])->name('update');
    Route::delete('{organizationStructure}', [OrganizationStructuresController::class, 'destroy'])->name('destroy');
    Route::patch('{organizationStructure}/restore', [OrganizationStructuresController::class, 'restore'])->withTrashed()->name('restore');
    Route::delete('{organizationStructure}/force', [OrganizationStructuresController::class, 'forceDestroy'])->withTrashed()->name('force-destroy');
});
