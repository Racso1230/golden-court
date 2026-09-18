<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Moderation
    |--------------------------------------------------------------------------
    |
    | auto_flag_threshold: once a published review has this many unresolved
    | flags it is moved to "flagged" automatically and hidden until an admin
    | looks at it.
    |
    */

    'moderation' => [
        'auto_flag_threshold' => (int) env('GOLDEN_COURT_AUTO_FLAG_THRESHOLD', 3),
    ],

];
