<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UsageLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [ProjectController::class, 'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::resource('projects', ProjectController::class);
    
    Route::get('/projects/{project}/usage-logs', [UsageLogController::class, 'index'])->name('projects.usage-logs.index');
    Route::get('/projects/{project}/usage-logs/export', [UsageLogController::class, 'export'])->name('projects.usage-logs.export');

    Route::get('/projects/{project}/team', [App\Http\Controllers\TeamMemberController::class, 'index'])->name('projects.team.index');

    Route::get('/projects/{project}/api-keys', [ApiKeyController::class, 'index'])->name('projects.api-keys.index');
    Route::get('/projects/{project}/api-keys/create', [ApiKeyController::class, 'create'])->name('projects.api-keys.create');
    Route::get('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('projects.api-keys.show');
    Route::post('/projects/{project}/api-keys', [ApiKeyController::class, 'store'])->name('projects.api-keys.store');
    Route::delete('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('projects.api-keys.destroy');

    Route::get('/billing/pricing', [\App\Http\Controllers\BillingController::class, 'pricing'])->name('billing.pricing');
    Route::get('/billing/dashboard', [\App\Http\Controllers\BillingController::class, 'dashboard'])->name('billing.dashboard');
});
