<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | Every option is environment-driven so one file serves local development
    | (while `npm run dev` runs, PHP posts pages to the Vite dev server's
    | /__inertia_ssr endpoint), production (a Node process started with
    | `php artisan inertia:start-ssr`) and the test suite, where phpunit.xml
    | disables SSR so Pest never contacts a rendering gateway.
    |
    | mergeConfigFrom() merges top-level keys only, so this block must list all
    | of the package's SSR options or their defaults are silently dropped.
    |
    */

    'ssr' => [

        'enabled' => (bool) env('INERTIA_SSR_ENABLED', true),

        'runtime' => env('INERTIA_SSR_RUNTIME', 'node'),

        'ensure_runtime_exists' => (bool) env('INERTIA_SSR_ENSURE_RUNTIME_EXISTS', false),

        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        // Override when PHP cannot reach the address written to public/hot.
        'hot_url' => env('INERTIA_SSR_HOT_URL'),

        'ensure_bundle_exists' => (bool) env('INERTIA_SSR_ENSURE_BUNDLE_EXISTS', true),

        // What `vp build --ssr` emits for the resources/js/app.ts entry. While the
        // file is missing, requests are rendered on the client instead.
        'bundle' => base_path('bootstrap/ssr/app.js'),

        // Fall back to client-side rendering on failure unless told to fail loudly.
        'throw_on_error' => (bool) env('INERTIA_SSR_THROW_ON_ERROR', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Where page components live and which extensions count, used both when
    | rendering (if `ensure_pages_exist` is on) and by the test assertions.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [
            resource_path('js/pages'),
        ],

        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | `assertInertia` resolves the asserted component as a file under
    | `pages.paths`, so a test that names a page fails if the file is missing.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Expose Shared Prop Keys
    |--------------------------------------------------------------------------
    |
    | Each page response lists the top-level keys registered through
    | Inertia::share so the client can carry them over during instant visits.
    |
    */

    'expose_shared_prop_keys' => true,

    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    'history' => [

        'encrypt' => (bool) env('INERTIA_ENCRYPT_HISTORY', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | DevTools
    |--------------------------------------------------------------------------
    |
    | Records one entry per request to disk so the DevTools Chrome extension
    | may read it back over HTTP. Recording is limited to the local environment.
    |
    */

    'devtools' => [

        'enabled' => env('INERTIA_DEVTOOLS_ENABLED'),

        'except' => ['telescope*', 'horizon*', '_inertia/devtools*'],

        'storage' => [

            'path' => storage_path('inertia-devtools'),

            'ttl' => (int) env('INERTIA_DEVTOOLS_TTL_HOURS', 24),

            'prune_interval' => (int) env('INERTIA_DEVTOOLS_PRUNE_INTERVAL_SECONDS', 300),

            'limit' => (int) env('INERTIA_DEVTOOLS_LIMIT', 100),

        ],

        'middleware' => ['web'],

        'gate' => env('INERTIA_DEVTOOLS_GATE'),

        'redact' => [

            'keys' => [
                'password',
                'password_confirmation',
                'current_password',
                'token',
                '_token',
                'access_token',
                'refresh_token',
                'secret',
                'client_secret',
                'api_key',
            ],

            'headers' => [
                'cookie',
                'set-cookie',
                'authorization',
                'proxy-authorization',
                'x-xsrf-token',
                'x-csrf-token',
            ],

        ],

    ],

];
