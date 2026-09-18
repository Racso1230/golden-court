<?php

declare(strict_types=1);

use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;

it('builds the read model from a review', function (): void {
    $review = Review::factory()
        ->for(User::factory()->create(['display_name' => 'padel_pat']))
        ->create(['glass_rating' => 5, 'lighting_rating' => 4, 'turf_rating' => 3, 'facilities_rating' => 2, 'helpful_count' => 7]);

    $data = ReviewData::fromModel($review);

    expect($data->id)->toBe($review->id)
        ->and($data->courtId)->toBe($review->court_id)
        ->and($data->scores)->toBe(['glass' => 5, 'lighting' => 4, 'turf' => 3, 'facilities' => 2])
        ->and($data->overall)->toBe(3.5)
        ->and($data->status)->toBe(ReviewStatus::Published)
        ->and($data->authorDisplayName)->toBe('padel_pat')
        ->and($data->helpfulCount)->toBe(7)
        ->and($data->hasVoted)->toBeFalse()
        ->and($data->reply)->toBeNull();
});

it('includes the owner reply when present', function (): void {
    $reply = ReviewReply::factory()->for(User::factory()->create(['display_name' => 'club_owner']))->create();

    $data = ReviewData::fromModel($reply->review);

    expect($data->reply?->id)->toBe($reply->id)
        ->and($data->reply?->authorDisplayName)->toBe('club_owner')
        ->and($data->reply?->body)->toBe($reply->body);
});

it('knows whether the viewer has voted the review helpful', function (): void {
    $vote = ReviewVote::factory()->create();
    $stranger = User::factory()->create();

    expect(ReviewData::fromModel($vote->review, $vote->user)->hasVoted)->toBeTrue()
        ->and(ReviewData::fromModel($vote->review, $stranger)->hasVoted)->toBeFalse()
        ->and(ReviewData::fromModel($vote->review->load('votes'), $vote->user)->hasVoted)->toBeTrue();
});

it('serialises enums and dates for the frontend', function (): void {
    $review = Review::factory()->create(['played_on' => '2026-08-15']);

    $array = ReviewData::fromModel($review)->toArray();

    expect($array['status'])->toBe('published')
        ->and($array['playedOn'])->toStartWith('2026-08-15')
        ->and($array)->toHaveKeys(['id', 'scores', 'overall', 'body', 'authorDisplayName', 'createdAt', 'helpfulCount', 'hasVoted', 'reply']);
});
