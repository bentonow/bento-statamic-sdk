<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bento Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Bento integration settings here.
    |
    */

    'enabled' => env('BENTO_ENABLED', false),

    'site_uuid' => env('BENTO_SITE_UUID', ''),

    'publishable_key' => env('BENTO_PUBLISHABLE_KEY', ''),

    'secret_key' => env('BENTO_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    |
    | Configure advanced features of the Bento integration.
    |
    */

    'auto_user_sync' => env('BENTO_AUTO_USER_SYNC', true),

    'inject_js' => env('BENTO_INJECT_JS', false),
];
