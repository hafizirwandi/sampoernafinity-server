<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfNotAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');

// Customer-facing area: creators log in here to see their own dashboard,
// active packages, and the manual book. Admin/staff accounts are redirected
// out by RedirectIfAdmin — this area is not for them.
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');

    Route::get('/login/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/login/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', RedirectIfAdmin::class])->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');
});

// Admin area: separate login address, not linked from the public site.
// RedirectIfNotAdmin sends any non-admin account straight back to /dashboard.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::livewire('/login', 'admin.auth.login')->name('login');
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->middleware('auth')->name('logout');

    Route::middleware(['auth', RedirectIfNotAdmin::class])->group(function () {
        Route::livewire('/dashboard', 'admin.dashboard')->name('dashboard');

        Route::middleware('permission:manage users')->group(function () {
            Route::livewire('/users', 'admin.users.index')->name('users.index');
        });

        Route::middleware('permission:manage admin users')->group(function () {
            Route::livewire('/admin-users', 'admin.admin-users.index')->name('admin-users.index');
        });

        Route::middleware('permission:manage roles')->group(function () {
            Route::livewire('/roles', 'admin.roles.index')->name('roles.index');
        });

        Route::middleware('permission:manage features')->group(function () {
            Route::livewire('/features', 'admin.features.index')->name('features.index');
        });

        Route::middleware('permission:manage packages')->group(function () {
            Route::livewire('/packages', 'admin.packages.index')->name('packages.index');
        });

        Route::middleware('permission:manage subscriptions')->group(function () {
            Route::livewire('/subscriptions', 'admin.subscriptions.index')->name('subscriptions.index');
        });

        Route::middleware('permission:manage gifts')->group(function () {
            Route::livewire('/gifts', 'admin.gifts.index')->name('gifts.index');
        });
    });
});
