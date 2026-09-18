<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\Models\Review;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A review teaser for the home page, with enough context to link to its court.
 */
#[TypeScript]
final class RecentReviewData extends Data
{
    public function __construct(
        public int $id,
        public float $overall,
        public string $excerpt,
        public string $authorDisplayName,
        public string $courtName,
        public string $courtSlug,
        public string $venueName,
        public string $venueSlug,
        public CarbonImmutable $createdAt,
    ) {}

    /**
     * Expects `user` and `court.venue` to be eager loaded.
     */
    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            overall: $review->scores()->overall(),
            excerpt: Str::limit($review->body, 160),
            authorDisplayName: $review->user->display_name,
            courtName: $review->court->name,
            courtSlug: $review->court->slug,
            venueName: $review->court->venue->name,
            venueSlug: $review->court->venue->slug,
            createdAt: $review->created_at ?? CarbonImmutable::now(),
        );
    }
}
