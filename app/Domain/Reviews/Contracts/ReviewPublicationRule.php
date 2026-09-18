<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Contracts;

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

/**
 * Decides whether a freshly submitted review goes live immediately or waits
 * for moderation. Swappable so the trust model can change without touching
 * the submission flow.
 */
interface ReviewPublicationRule
{
    public function shouldAutoPublish(User $user, Review $review): bool;
}
