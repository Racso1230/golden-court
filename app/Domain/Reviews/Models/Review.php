<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Models;

use App\Domain\Courts\Models\Court;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Casts\RatingCast;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\ValueObjects\CourtScores;
use App\Domain\Reviews\ValueObjects\Rating;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property int $user_id
 * @property int $court_id
 * @property Rating $glass_rating
 * @property Rating $lighting_rating
 * @property Rating $turf_rating
 * @property Rating $facilities_rating
 * @property string $body
 * @property ReviewStatus $status
 * @property CarbonImmutable|null $played_on
 * @property int $helpful_count
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User $user
 * @property-read Court $court
 */
#[Fillable([
    'user_id',
    'court_id',
    'glass_rating',
    'lighting_rating',
    'turf_rating',
    'facilities_rating',
    'body',
    'played_on',
])]
#[UseFactory(ReviewFactory::class)]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, Prunable, SoftDeletes;

    /**
     * Soft-deleted reviews are kept for a retention window, then removed for
     * good by the daily `model:prune` run.
     *
     * @return Builder<Review>
     */
    public function prunable(): Builder
    {
        return static::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(Config::integer('golden_court.retention.deleted_reviews_days')));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'glass_rating' => RatingCast::class,
            'lighting_rating' => RatingCast::class,
            'turf_rating' => RatingCast::class,
            'facilities_rating' => RatingCast::class,
            'status' => ReviewStatus::class,
            'played_on' => 'immutable_date',
            'helpful_count' => 'integer',
        ];
    }

    /**
     * The four ratings as one value object.
     */
    public function scores(): CourtScores
    {
        return new CourtScores(
            $this->glass_rating,
            $this->lighting_rating,
            $this->turf_rating,
            $this->facilities_rating,
        );
    }

    public function isPublished(): bool
    {
        return $this->status->isVisible();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * @param  Builder<Review>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ReviewStatus::Published);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * @return HasOne<ReviewReply, $this>
     */
    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class);
    }

    /**
     * @return HasMany<ReviewFlag, $this>
     */
    public function flags(): HasMany
    {
        return $this->hasMany(ReviewFlag::class);
    }

    /**
     * @return HasMany<ReviewVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }
}
