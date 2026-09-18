<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Data;

use App\Domain\Moderation\Enums\FlagReason;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class FlagReviewData extends Data
{
    public function __construct(
        public FlagReason $reason,
        public ?string $details = null,
    ) {}
}
