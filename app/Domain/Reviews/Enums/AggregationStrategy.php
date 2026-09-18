<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum AggregationStrategy: string implements HasLabel
{
    case Simple = 'simple';
    case Bayesian = 'bayesian';

    public function label(): string
    {
        return match ($this) {
            self::Simple => 'Simple average',
            self::Bayesian => 'Bayesian average',
        };
    }
}
