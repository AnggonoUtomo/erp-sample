<?php

use App\Modules\Console\ActivityCenters\Http\Controllers\ActivityCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('activity-center/read', [ActivityCenterController::class, 'markAsRead'])->name('activity-center.read');
});
