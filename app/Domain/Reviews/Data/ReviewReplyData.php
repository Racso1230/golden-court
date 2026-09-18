<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\Models\ReviewReply;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Read model for a venue owner's reply as shown under a review.
 */
#[TypeScript]
final class ReviewReplyData extends Data
{
    public function __construct(
        public int $id,
        public string $body,
        public string $authorDisplayName,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromModel(ReviewReply $reply): self
    {
        $reply->loadMissing('user');

        return new self(
            id: $reply->id,
            body: $reply->body,
            authorDisplayName: $reply->user->display_name,
            createdAt: $reply->created_at ?? CarbonImmutable::now(),
        );
    }
}
