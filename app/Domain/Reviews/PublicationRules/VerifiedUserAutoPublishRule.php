<?php

declare(strict_types=1);

namespace App\Domain\Reviews\PublicationRules;

use App\Domain\Reviews\Contracts\ReviewPublicationRule;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

/**
 * Trust verified users with a clean record; everyone else waits for a moderator.
 */
final class VerifiedUserAutoPublishRule implements ReviewPublicationRule
{
    public function shouldAutoPublish(User $user, Review $review): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return ! $user->reviews()
            ->withTrashed()
            ->whereKeyNot($review->id)
            ->where('status', ReviewStatus::Removed)
            ->exists();
    }
}
