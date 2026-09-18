<?php

declare(strict_types=1);

namespace App\Domain\Claims\Data;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * A claim as shown to admins.
 */
final class VenueClaimData extends Data
{
    public function __construct(
        public int $id,
        public ClaimStatus $status,
        public string $statusLabel,
        public int $venueId,
        public string $venueName,
        public string $venueSlug,
        public string $claimantDisplayName,
        public string $claimantEmail,
        public string $evidence,
        public CarbonImmutable $submittedAt,
        public ?CarbonImmutable $reviewedAt,
        public ?string $rejectionReason,
    ) {}

    public static function fromModel(VenueClaim $claim): self
    {
        $claim->loadMissing(['venue', 'user']);

        return new self(
            id: $claim->id,
            status: $claim->status,
            statusLabel: $claim->status->label(),
            venueId: $claim->venue->id,
            venueName: $claim->venue->name,
            venueSlug: $claim->venue->slug,
            claimantDisplayName: $claim->user->display_name,
            claimantEmail: $claim->user->email,
            evidence: $claim->evidence,
            submittedAt: $claim->created_at ?? CarbonImmutable::now(),
            reviewedAt: $claim->reviewed_at,
            rejectionReason: $claim->rejection_reason,
        );
    }
}
