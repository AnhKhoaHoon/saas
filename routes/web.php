<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [ProjectController::class, 'index'])->name('home');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/api-keys', [ApiKeyController::class, 'index'])->name('projects.api-keys.index');
    Route::get('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('projects.api-keys.show');
    Route::post('/projects/{project}/api-keys', [ApiKeyController::class, 'store'])->name('projects.api-keys.store');
    Route::delete('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('projects.api-keys.destroy');
});
