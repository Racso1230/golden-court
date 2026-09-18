<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ReplyToReviewData extends Data
{
    public function __construct(
        public string $body,
    ) {}
}
