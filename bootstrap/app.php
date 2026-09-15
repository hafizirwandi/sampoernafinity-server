<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Keep the admin area and the customer area on separate login pages.
        // Guests are routed by which area they tried to reach...
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin/*') ? route('admin.login') : route('login')
        );
        // ...but an already-authenticated user is routed by whether their role
        // grants the "access admin" permission, so an admin hitting /login (or
        // any guest page) still lands on their own dashboard, and vice versa.
        $middleware->redirectUsersTo(
            fn (Request $request) => $request->user()?->can('access admin')
                ? route('admin.dashboard')
                : route('dashboard')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
