<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('console/dashboard');
    })->name('dashboard');

    Route::get('hr', fn () => redirect()->route('hr.dashboard'));

    Route::get('hr/dashboard', function () {
        return Inertia::render('hr/dashboard');
    })->middleware('can:hr.view')->name('hr.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
