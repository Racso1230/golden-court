<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Policies;

use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository;

/**
 * New accounts must wait before reviewing, which slows down throwaway
 * accounts created to pump a venue. Shared by ReviewPolicy, which enforces
 * it, and the court page, which tells a new player when they may review.
 */
final readonly class MinimumAccountAge
{
    public function __construct(private Repository $config) {}

    public function isMet(User $user): bool
    {
        return $this->reviewableFrom($user) === null;
    }

    /**
     * When the account becomes old enough to review, or null if it already is.
     */
    public function reviewableFrom(User $user): ?CarbonImmutable
    {
        $minimumHours = $this->config->integer('golden_court.reviews.min_account_age_hours');

        if ($minimumHours <= 0 || $user->created_at === null) {
            return null;
        }

        $from = CarbonImmutable::instance($user->created_at)->addHours($minimumHours);

        return $from->isFuture() ? $from : null;
    }
}
