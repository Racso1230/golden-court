<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Average rating per dimension across a court's published reviews. Null
 * when there are no published reviews to average.
 */
#[TypeScript]
final class DimensionAveragesData extends Data
{
    public function __construct(
        public ?float $glass,
        public ?float $lighting,
        public ?float $turf,
        public ?float $facilities,
    ) {}

    public static function none(): self
    {
        return new self(null, null, null, null);
    }
}
