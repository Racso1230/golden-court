<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ModerationCountsData extends Data
{
    public function __construct(
        public int $pendingClaims,
        public int $flaggedReviews,
        public int $pendingReviews,
    ) {}
}
