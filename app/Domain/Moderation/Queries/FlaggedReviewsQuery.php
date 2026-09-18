<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Queries;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Reviews with at least one unresolved flag, most-flagged first. Removed
 * reviews are left out: there is nothing more to decide about them.
 */
final class FlaggedReviewsQuery
{
    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Review::query()
            ->whereIn('status', [ReviewStatus::Published, ReviewStatus::Flagged])
            ->whereHas('flags', fn (Builder $flags): Builder => $flags->whereNull('resolved_at'))
            ->withCount(['flags as unresolved_flags_count' => fn (Builder $flags): Builder => $flags->whereNull('resolved_at')])
            ->with([
                'user',
                'court.venue',
                'reply.user',
                'flags' => fn (Relation $flags): Relation => $flags->whereNull('resolved_at')->with('user'),
            ])
            ->orderByDesc('unresolved_flags_count')
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }
}
