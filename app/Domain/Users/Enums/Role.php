<?php

declare(strict_types=1);

namespace App\Domain\Users\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case Player = 'player';
    case VenueOwner = 'venue_owner';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Player => 'Player',
            self::VenueOwner => 'Venue owner',
            self::Admin => 'Admin',
        };
    }
}
