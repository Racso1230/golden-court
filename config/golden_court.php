<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Aggregation
    |--------------------------------------------------------------------------
    |
    | strategy: which RatingAggregator implementation computes court and venue
    | scores. "simple" is the plain mean of review overalls; "bayesian" pulls
    | courts with few reviews towards the site-wide mean so one 5-star review
    | cannot outrank twenty 4.6-star ones. After changing it run
    | `php artisan golden-court:recalculate-scores`.
    |
    | bayesian_confidence: the C in (C*m + sum) / (C + n). Roughly "how many
    | reviews a court needs before its own average outweighs the prior".
    |
    */

    'aggregation' => [
        'strategy' => env('GOLDEN_COURT_AGGREGATION', 'bayesian'),
        'bayesian_confidence' => (int) env('GOLDEN_COURT_BAYESIAN_CONFIDENCE', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    |
    | min_account_age_hours: how long after registering a user may write a
    | review. Slows down throwaway accounts created to pump a venue.
    |
    */

    'reviews' => [
        'min_account_age_hours' => (int) env('GOLDEN_COURT_MIN_ACCOUNT_AGE_HOURS', 1),
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | How long soft-deleted reviews and resolved flags are kept before the
    | daily `model:prune` run removes them for good.
    |
    */

    'retention' => [
        'deleted_reviews_days' => (int) env('GOLDEN_COURT_RETAIN_DELETED_REVIEWS_DAYS', 90),
        'resolved_flags_days' => (int) env('GOLDEN_COURT_RETAIN_RESOLVED_FLAGS_DAYS', 180),
    ],

];
