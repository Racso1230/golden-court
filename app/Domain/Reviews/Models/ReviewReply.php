<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Models;

use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewReplyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A venue owner's single public response to a review.
 *
 * @property int $id
 * @property int $review_id
 * @property int $user_id
 * @property string $body
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Review $review
 * @property-read User $user
 */
#[Fillable(['review_id', 'user_id', 'body'])]
#[UseFactory(ReviewReplyFactory::class)]
class ReviewReply extends Model
{
    /** @use HasFactory<ReviewReplyFactory> */
    use HasFactory;

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * @return BelongsTo<Review, $this>
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
