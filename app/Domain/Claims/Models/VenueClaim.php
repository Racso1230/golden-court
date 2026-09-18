<?php

declare(strict_types=1);

namespace App\Domain\Claims\Models;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Carbon\CarbonImmutable;
use Database\Factories\VenueClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's request to be recognised as the owner of a venue.
 *
 * @property int $id
 * @property int $venue_id
 * @property int $user_id
 * @property ClaimStatus $status
 * @property string $evidence
 * @property int|null $reviewed_by_user_id
 * @property CarbonImmutable|null $reviewed_at
 * @property string|null $rejection_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['venue_id', 'user_id', 'evidence'])]
#[UseFactory(VenueClaimFactory::class)]
class VenueClaim extends Model
{
    /** @use HasFactory<VenueClaimFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === ClaimStatus::Pending;
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * The user making the claim.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who approved or rejected the claim, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
