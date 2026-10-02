<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Enums\Role;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Read model for a reply as shown under a review: normally the venue's owner
 * responding, occasionally an admin.
 */
#[TypeScript]
final class ReviewReplyData extends Data
{
    public function __construct(
        public int $id,
        public string $body,
        public string $authorDisplayName,
        /** True when the venue's owner wrote it; owners can only reply on their own venue. */
        public bool $fromOwner,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromModel(ReviewReply $reply): self
    {
        $reply->loadMissing('user');

        return new self(
            id: $reply->id,
            body: $reply->body,
            authorDisplayName: $reply->user->display_name,
            fromOwner: $reply->user->role === Role::VenueOwner,
            createdAt: $reply->created_at ?? CarbonImmutable::now(),
        );
    }
}
