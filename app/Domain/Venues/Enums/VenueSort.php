<?php

declare(strict_types=1);

namespace App\Domain\Venues\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum VenueSort: string implements HasLabel
{
    case Score = 'score';
    case Reviews = 'reviews';
    case Distance = 'distance';
    case Name = 'name';

    public function label(): string
    {
        return match ($this) {
            self::Score => 'Highest rated',
            self::Reviews => 'Most reviewed',
            self::Distance => 'Nearest',
            self::Name => 'Name',
        };
    }
}
