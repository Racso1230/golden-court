<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\DB;

final class DeleteReviewAction
{
    public function handle(Review $review): void
    {
        DB::transaction(function () use ($review): void {
            $courtId = $review->court_id;

            $review->delete();

            DB::afterCommit(fn () => ReviewDeleted::dispatch($courtId));
        });
    }
}
