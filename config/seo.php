<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Site-wide metadata
    |--------------------------------------------------------------------------
    |
    | Defaults used by every page's head tags (see app/Support/Seo). The site
    | name comes from APP_NAME and absolute URLs from APP_URL.
    |
    */

    'description' => env(
        'SEO_DESCRIPTION',
        'Real players rate every padel court on glass, lighting, turf and facilities. Find the best venues near you and leave your own review.',
    ),

    // The html lang attribute and the Open Graph locale. APP_LOCALE stays `en`
    // because it drives translation lookup, not the language tag.
    'locale' => 'en-GB',
    'og_locale' => 'en_GB',

    'theme_color' => '#ffffff',

    /*
    |--------------------------------------------------------------------------
    | Open Graph image
    |--------------------------------------------------------------------------
    |
    | A site-relative path to a 1200x630 image used when a page is shared. Left
    | empty until the brand assets exist; pages then fall back to a summary card.
    |
    */

    'og_image' => [
        'path' => env('SEO_OG_IMAGE'),
        'width' => 1200,
        'height' => 630,
        'alt' => 'Golden Court: player reviews of padel courts',
    ],

    // An @handle for twitter:site, if the site ever has one.
    'twitter_site' => env('SEO_TWITTER_SITE'),

];
