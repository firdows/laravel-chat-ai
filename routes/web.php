<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('todos')->group(function () {
    Route::get('/', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/', [TodoController::class, 'store'])->name('todos.store');
    Route::put('/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::delete('/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');
})->middleware(['auth', 'verified']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
