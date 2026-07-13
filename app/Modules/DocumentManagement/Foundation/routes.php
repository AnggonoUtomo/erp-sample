<?php

use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentIngestionController;
use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentVersionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:20,1'])->prefix('document-management')->name('document-management.')->group(function (): void {
    Route::post('documents', DocumentIngestionController::class)->name('documents.store');
    Route::post('documents/{reference}/versions', [DocumentVersionController::class, 'store'])
        ->name('documents.versions.store');
});
