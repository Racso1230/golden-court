<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Contracts\ReviewPublicationRule;
use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Exceptions\ReviewAlreadyExistsException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class SubmitReviewAction
{
    public function __construct(private readonly ReviewPublicationRule $publicationRule) {}

    /**
     * @throws ReviewAlreadyExistsException when the user has already reviewed the court
     */
    public function handle(User $user, SubmitReviewData $data): Review
    {
        return DB::transaction(function () use ($user, $data): Review {
            $review = new Review;
            $review->fill([
                'court_id' => $data->courtId,
                'glass_rating' => $data->glass,
                'lighting_rating' => $data->lighting,
                'turf_rating' => $data->turf,
                'facilities_rating' => $data->facilities,
                'body' => $data->body,
                'played_on' => $data->playedOn,
            ]);
            $review->user()->associate($user);
            $review->forceFill(['status' => ReviewStatus::Pending]);

            try {
                $review->save();
            } catch (UniqueConstraintViolationException $exception) {
                // The database is the guard; this exception is the translation.
                throw ReviewAlreadyExistsException::forUserAndCourt($user->id, $data->courtId, $exception);
            }

            DB::afterCommit(fn () => ReviewSubmitted::dispatch($review->id, $review->court_id));

            if ($this->publicationRule->shouldAutoPublish($user, $review)) {
                $review->forceFill(['status' => ReviewStatus::Published])->save();

                DB::afterCommit(fn () => ReviewStatusChanged::dispatch(
                    $review->id,
                    $review->court_id,
                    ReviewStatus::Pending,
                    ReviewStatus::Published,
                ));
            }

            return $review;
        });
    }
}
