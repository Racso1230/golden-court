<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Read model for a review as sent to the frontend.
 */
final class ReviewData extends Data
{
    /**
     * @param  array{glass: int, lighting: int, turf: int, facilities: int}  $scores
     */
    public function __construct(
        public int $id,
        public int $courtId,
        public array $scores,
        public float $overall,
        public string $body,
        public ReviewStatus $status,
        public string $authorDisplayName,
        public ?CarbonImmutable $playedOn,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public int $helpfulCount,
        public bool $hasVoted,
        public ?ReviewReplyData $reply,
    ) {}

    /**
     * @param  User|null  $viewer  Used to work out whether they have voted this review helpful.
     */
    public static function fromModel(Review $review, ?User $viewer = null): self
    {
        $review->loadMissing(['user', 'reply.user']);

        $scores = $review->scores();

        return new self(
            id: $review->id,
            courtId: $review->court_id,
            scores: $scores->toArray(),
            overall: $scores->overall(),
            body: $review->body,
            status: $review->status,
            authorDisplayName: $review->user->display_name,
            playedOn: $review->played_on,
            createdAt: $review->created_at ?? CarbonImmutable::now(),
            updatedAt: $review->updated_at ?? CarbonImmutable::now(),
            helpfulCount: $review->helpful_count,
            hasVoted: self::hasVoted($review, $viewer),
            reply: $review->reply === null ? null : ReviewReplyData::fromModel($review->reply),
        );
    }

    private static function hasVoted(Review $review, ?User $viewer): bool
    {
        if ($viewer === null) {
            return false;
        }

        if ($review->relationLoaded('votes')) {
            return $review->votes->contains(fn (ReviewVote $vote): bool => $vote->user_id === $viewer->id);
        }

        return $review->votes()->whereBelongsTo($viewer)->exists();
    }
}
