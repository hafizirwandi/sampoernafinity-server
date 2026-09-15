<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Keep admin/staff accounts out of the customer area — they belong in
     * /admin/*, not in the creator-facing dashboard, packages, or manual book.
     * Checked by permission (not a hardcoded role name) so any role granted
     * "access admin" via the Role & Permission screen is covered.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->can('access admin')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
