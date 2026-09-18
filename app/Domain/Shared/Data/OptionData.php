<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data;

use App\Domain\Shared\Contracts\HasLabel;
use BackedEnum;
use Spatie\LaravelData\Data;

/**
 * A value/label pair for a select control.
 */
final class OptionData extends Data
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}

    /**
     * @param  class-string<BackedEnum&HasLabel>  $enum
     * @return list<self>
     */
    public static function fromEnum(string $enum): array
    {
        return array_map(
            static fn (BackedEnum&HasLabel $case): self => new self((string) $case->value, $case->label()),
            $enum::cases(),
        );
    }
}
