<?php

use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentDeliveryController;
use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentIngestionController;
use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentLifecycleController;
use App\Modules\DocumentManagement\Foundation\Http\Controllers\DocumentVersionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:20,1'])->prefix('document-management')->name('document-management.')->group(function (): void {
    Route::post('documents', DocumentIngestionController::class)->name('documents.store');
    Route::post('documents/{reference}/versions', [DocumentVersionController::class, 'store'])
        ->name('documents.versions.store');
    Route::delete('documents/{reference}', [DocumentLifecycleController::class, 'archive'])
        ->name('documents.archive');
    Route::patch('documents/{reference}/restore', [DocumentLifecycleController::class, 'restore'])
        ->name('documents.restore');
    Route::post('documents/{reference}/delivery', [DocumentDeliveryController::class, 'issue'])
        ->name('documents.delivery.issue');
    Route::post('deliveries/consume', [DocumentDeliveryController::class, 'consume'])
        ->name('deliveries.consume');
});
