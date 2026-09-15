<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GiftController;
use Illuminate\Support\Facades\Route;

// Token-based auth for the desktop app (Electron). Separate from the web
// session guard used by the browser/Livewire area.
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::get('/gifts', [GiftController::class, 'index'])->name('api.gifts');
});
