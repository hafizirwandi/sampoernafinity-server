<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Desktop App Deep Link Scheme
    |--------------------------------------------------------------------------
    |
    | After a successful Google/Discord login started from the Electron app,
    | the OAuth callback redirects the system browser to this custom URL
    | scheme so the desktop app can pick up the session again with a token.
    |
    */

    'scheme' => env('DESKTOP_APP_SCHEME', 'sampoernafinity'),

    /*
    |--------------------------------------------------------------------------
    | Release Folder
    |--------------------------------------------------------------------------
    |
    | Folder (relative to public/) holding the files electron-builder
    | produces for each Windows release: the Setup .exe, its .blockmap, and
    | latest.yml. The desktop app's auto-updater polls latest.yml in this
    | same folder, so uploading a new set here is all a release takes.
    | Upload latest.yml LAST so clients never see a version whose installer
    | isn't fully there yet.
    |
    */

    'release_path' => env('DESKTOP_RELEASE_PATH', 'downloads/desktop'),

    /*
    |--------------------------------------------------------------------------
    | Minimum Supported Version
    |--------------------------------------------------------------------------
    |
    | Desktop builds older than this get a 426 from the API and are shown a
    | blocking "update required" screen. Bump it only when an API change
    | breaks older builds; leave it empty to never force an update.
    |
    */

    'min_version' => env('DESKTOP_MIN_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | Platforms
    |--------------------------------------------------------------------------
    |
    | Only Windows ships today; the others are listed on the landing page as
    | "coming soon" until a build exists for them.
    |
    */

    'platforms' => [
        'windows' => ['label' => 'Windows', 'requirement' => 'Windows 10 / 11 (64-bit)', 'available' => true],
        'macos' => ['label' => 'macOS', 'requirement' => 'macOS 11 ke atas', 'available' => false],
        'linux' => ['label' => 'Linux', 'requirement' => 'AppImage / .deb', 'available' => false],
    ],

];
