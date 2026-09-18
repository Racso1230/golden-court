<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Policies;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;

final class ReviewPolicy
{
    /**
     * Players (and admins) may review a court they have not reviewed yet.
     * This is a UX pre-check; the database still enforces uniqueness.
     */
    public function create(User $user, Court $court): bool
    {
        if ($user->role !== Role::Player && ! $user->isAdmin()) {
            return false;
        }

        return ! $user->reviews()->where('court_id', $court->id)->exists();
    }

    public function update(User $user, Review $review): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $review->isOwnedBy($user)
            && in_array($review->status, [ReviewStatus::Pending, ReviewStatus::Published], true);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->isOwnedBy($user);
    }

    public function changeStatus(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    /**
     * Anyone signed in may find a published review helpful, except its author.
     */
    public function vote(User $user, Review $review): bool
    {
        return $review->isPublished() && ! $review->isOwnedBy($user);
    }

    /**
     * Anyone signed in may flag a published review once, except its author.
     */
    public function flag(User $user, Review $review): bool
    {
        if (! $review->isPublished() || $review->isOwnedBy($user)) {
            return false;
        }

        return ! $review->flags()->where('user_id', $user->id)->exists();
    }
}
