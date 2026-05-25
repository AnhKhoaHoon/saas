<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\UsageLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/language/{locale}', [LocaleController::class, 'switch'])->name('language.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [ProjectController::class, 'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::resource('projects', ProjectController::class);

    Route::get('/projects/{project}/usage-logs', [UsageLogController::class, 'index'])->name('projects.usage-logs.index');
    Route::get('/projects/{project}/usage-logs/export', [UsageLogController::class, 'export'])->name('projects.usage-logs.export');

    Route::get('/projects/{project}/team', [TeamMemberController::class, 'index'])->name('projects.team.index');
    Route::post('/projects/{project}/team/invites', [TeamInviteController::class, 'store'])->name('projects.team-invites.store');
    Route::delete('/projects/{project}/team/invites/{teamInvite}', [TeamInviteController::class, 'destroy'])->name('projects.team-invites.destroy');
    Route::get('/team-invites/{token}/accept', [TeamInviteController::class, 'accept'])->name('team-invites.accept');

    Route::get('/projects/{project}/api-keys', [ApiKeyController::class, 'index'])->name('projects.api-keys.index');
    Route::get('/projects/{project}/api-keys/create', [ApiKeyController::class, 'create'])->name('projects.api-keys.create');
    Route::get('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('projects.api-keys.show');
    Route::post('/projects/{project}/api-keys', [ApiKeyController::class, 'store'])->name('projects.api-keys.store');
    Route::delete('/projects/{project}/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('projects.api-keys.destroy');

    Route::get('/billing/pricing', [BillingController::class, 'pricing'])->name('billing.pricing');
    Route::get('/billing/dashboard', [BillingController::class, 'dashboard'])->name('billing.dashboard');
    Route::post('/billing/plan', [BillingController::class, 'changePlan'])->name('billing.plan.change');
});
