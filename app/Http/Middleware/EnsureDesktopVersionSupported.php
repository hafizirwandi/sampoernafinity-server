<?php

namespace App\Http\Middleware;

use App\Support\DesktopRelease;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects API calls from desktop builds older than DESKTOP_MIN_VERSION with
 * 426 Upgrade Required, which the app turns into a blocking update screen.
 * Requests without the X-App-Version header, and the version check
 * endpoint itself, pass through untouched.
 */
class EnsureDesktopVersionSupported
{
    public function handle(Request $request, Closure $next): Response
    {
        $version = $request->header('X-App-Version');

        if ($version && ! $request->routeIs('api.app.version') && DesktopRelease::isUnsupported($version)) {
            return response()->json([
                'message' => 'Versi aplikasi ini sudah tidak didukung. Silakan update ke versi terbaru.',
                'min_version' => DesktopRelease::minVersion(),
                'download_url' => route('download.windows'),
            ], 426);
        }

        return $next($request);
    }
}
