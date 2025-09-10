<?php

use App\Http\Controllers\RagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/rag/upload', [RagController::class, 'upload'])->name('rag.upload');
