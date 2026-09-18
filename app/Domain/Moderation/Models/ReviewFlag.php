<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Models;

use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFlagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user reporting a review for moderation.
 *
 * @property int $id
 * @property int $review_id
 * @property int $user_id
 * @property FlagReason $reason
 * @property string|null $details
 * @property CarbonImmutable|null $resolved_at
 * @property int|null $resolved_by_user_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['review_id', 'user_id', 'reason', 'details'])]
#[UseFactory(ReviewFlagFactory::class)]
class ReviewFlag extends Model
{
    /** @use HasFactory<ReviewFlagFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => FlagReason::class,
            'resolved_at' => 'immutable_datetime',
        ];
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /**
     * @return BelongsTo<Review, $this>
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * The user who raised the flag.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who resolved the flag, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
