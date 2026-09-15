<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAdmin
{
    /**
     * Keep customer accounts out of the admin area — send them back to
     * their own dashboard instead of a bare 403 when they hit /admin/*.
     * Checked by permission (not a hardcoded role name) so any role granted
     * "access admin" via the Role & Permission screen is covered.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->can('access admin')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
