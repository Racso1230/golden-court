<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Policies;

use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;

final class ReviewReplyPolicy
{
    /**
     * The owner of the venue the review is about (or an admin) may reply to
     * a published review.
     */
    public function create(User $user, Review $review): bool
    {
        if (! $review->isPublished()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $review->loadMissing('court.venue');

        return $review->court->venue->isOwnedBy($user);
    }

    public function update(User $user, ReviewReply $reply): bool
    {
        return $user->isAdmin() || $reply->isOwnedBy($user);
    }

    public function delete(User $user, ReviewReply $reply): bool
    {
        return $user->isAdmin() || $reply->isOwnedBy($user);
    }
}
