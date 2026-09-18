<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Data;

use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Moderation\Models\ReviewFlag;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ReviewFlagData extends Data
{
    public function __construct(
        public int $id,
        public FlagReason $reason,
        public string $reasonLabel,
        public ?string $details,
        public string $reporterDisplayName,
        public CarbonImmutable $createdAt,
        public ?CarbonImmutable $resolvedAt,
    ) {}

    public static function fromModel(ReviewFlag $flag): self
    {
        $flag->loadMissing('user');

        return new self(
            id: $flag->id,
            reason: $flag->reason,
            reasonLabel: $flag->reason->label(),
            details: $flag->details,
            reporterDisplayName: $flag->user->display_name,
            createdAt: $flag->created_at ?? CarbonImmutable::now(),
            resolvedAt: $flag->resolved_at,
        );
    }
}
