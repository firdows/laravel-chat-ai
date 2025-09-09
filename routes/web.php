<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('dashboard', [DashboardController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::prefix('dashboard')->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('dashboard');
    // Route::post('/chat', [DashboardController::class, 'chat'])->name('dashboard.chat');
})->middleware(['auth', 'verified']);

Route::prefix('chat')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/', [ChatController::class, 'store'])->name('chat.store');
    Route::post('/message', [ChatController::class, 'chat'])->name('chat.message');
})->middleware(['auth', 'verified']);

Route::prefix('todos')->group(function () {
    Route::get('/', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/', [TodoController::class, 'store'])->name('todos.store');
    Route::put('/{todo}', [TodoController::class, 'update'])->name('todos.update');
    Route::delete('/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');
})->middleware(['auth', 'verified']);


Route::get('/test', [TestController::class, 'index'])->name('test');
Route::post('/test/chat', [TestController::class, 'chat'])->name('test.chat');
Route::post('/test', [TestController::class, 'store'])->name('test.store');
// Route::post('/api/chat', [TestController::class, 'store'])->name('chat');

Route::prefix('about')->group(function () {
    Route::get('', [AboutController::class, 'index'])->name('about');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
