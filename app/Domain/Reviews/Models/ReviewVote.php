<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Models;

use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewVoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user marking a review as helpful.
 *
 * @property int $id
 * @property int $review_id
 * @property int $user_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['review_id', 'user_id'])]
#[UseFactory(ReviewVoteFactory::class)]
class ReviewVote extends Model
{
    /** @use HasFactory<ReviewVoteFactory> */
    use HasFactory;

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
