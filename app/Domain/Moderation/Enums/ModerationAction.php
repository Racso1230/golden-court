<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum ModerationAction: string implements HasLabel
{
    case ReviewStatusChanged = 'review_status_changed';
    case ReviewFlagsResolved = 'review_flags_resolved';
    case ClaimApproved = 'claim_approved';
    case ClaimRejected = 'claim_rejected';

    public function label(): string
    {
        return match ($this) {
            self::ReviewStatusChanged => 'Review status changed',
            self::ReviewFlagsResolved => 'Flags resolved',
            self::ClaimApproved => 'Claim approved',
            self::ClaimRejected => 'Claim rejected',
        };
    }
}
