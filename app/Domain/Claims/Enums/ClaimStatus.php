<?php

declare(strict_types=1);

namespace App\Domain\Claims\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum ClaimStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
