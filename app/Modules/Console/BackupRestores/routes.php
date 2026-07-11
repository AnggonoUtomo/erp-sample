<?php

use App\Modules\Console\BackupRestores\Http\Controllers\BackupRestoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('backup-restore')->name('backup-restore.')->group(function () {
    Route::get('/', [BackupRestoreController::class, 'index'])->name('index');
    Route::get('export', [BackupRestoreController::class, 'export'])->name('export');
    Route::post('restore', [BackupRestoreController::class, 'restore'])->name('restore');
    Route::get('full/export', [BackupRestoreController::class, 'fullExport'])->name('full.export');
    Route::post('full/restore', [BackupRestoreController::class, 'fullRestore'])->middleware('throttle:3,10')->name('full.restore');
});
