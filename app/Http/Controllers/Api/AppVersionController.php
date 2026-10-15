<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DesktopRelease;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    /**
     * Checked by the desktop app on startup (before login) to learn whether
     * its build is still supported, independent of the auto-updater feed.
     */
    public function show(): JsonResponse
    {
        $release = DesktopRelease::latest();

        return response()->json([
            'latest_version' => $release['version'] ?? null,
            'min_version' => DesktopRelease::minVersion(),
            'download_url' => route('download.windows'),
        ]);
    }
}
