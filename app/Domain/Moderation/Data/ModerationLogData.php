<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Data;

use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Models\ModerationLog;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ModerationLogData extends Data
{
    /**
     * @param  array<string, string|int|float|bool|null>  $details
     */
    public function __construct(
        public int $id,
        public ModerationAction $action,
        public string $actionLabel,
        public string $actorLabel,
        public ?string $actorDisplayName,
        public array $details,
        public CarbonImmutable $createdAt,
    ) {}

    /**
     * Expects `actor` to be eager loaded.
     */
    public static function fromModel(ModerationLog $log): self
    {
        return new self(
            id: $log->id,
            action: $log->action,
            actionLabel: $log->action->label(),
            actorLabel: $log->actor_label,
            actorDisplayName: $log->actor?->display_name,
            details: $log->details ?? [],
            createdAt: $log->created_at,
        );
    }
}
