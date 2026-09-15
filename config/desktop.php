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

];
