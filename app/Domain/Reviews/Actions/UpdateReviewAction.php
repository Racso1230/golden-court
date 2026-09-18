<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Data\UpdateReviewData;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\DB;

final class UpdateReviewAction
{
    public function handle(Review $review, UpdateReviewData $data): Review
    {
        return DB::transaction(function () use ($review, $data): Review {
            $review->fill([
                'glass_rating' => $data->glass,
                'lighting_rating' => $data->lighting,
                'turf_rating' => $data->turf,
                'facilities_rating' => $data->facilities,
                'body' => $data->body,
                'played_on' => $data->playedOn,
            ]);
            $review->save();

            DB::afterCommit(fn () => ReviewUpdated::dispatch($review->id, $review->court_id));

            return $review;
        });
    }
}
