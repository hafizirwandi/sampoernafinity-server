<?php

namespace App\Http\Controllers;

use App\Support\DesktopRelease;
use Illuminate\Http\RedirectResponse;

class DesktopDownloadController extends Controller
{
    /**
     * Stable link to the newest Windows installer, so the landing page (and
     * anything shared elsewhere) never has to change between releases.
     */
    public function windows(): RedirectResponse
    {
        $release = DesktopRelease::latest();

        if (! $release) {
            return redirect()
                ->to(route('landing').'#download')
                ->with('download_status', 'Installer Windows belum tersedia. Coba lagi nanti.');
        }

        return redirect()->away($release['url']);
    }
}
