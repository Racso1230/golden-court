<?php

declare(strict_types=1);

use App\Domain\Reviews\Exceptions\InvalidRatingException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\ValueObjects\CourtScores;
use App\Domain\Reviews\ValueObjects\Rating;
use Illuminate\Support\Facades\DB;

it('casts an integer column to a Rating', function (): void {
    $review = Review::factory()->create(['glass_rating' => 4]);

    $fresh = Review::query()->whereKey($review->id)->firstOrFail();

    expect($fresh->glass_rating)->toBeInstanceOf(Rating::class)
        ->and($fresh->glass_rating->value)->toBe(4);
});

it('writes a Rating back as an integer', function (): void {
    $review = Review::factory()->create(['turf_rating' => 2]);

    $review->turf_rating = Rating::from(5);
    $review->save();

    expect(DB::table('reviews')->where('id', $review->id)->value('turf_rating'))->toBe(5);
});

it('accepts a plain integer and validates it on the way in', function (): void {
    $review = Review::factory()->create();

    $review->lighting_rating = 3;

    expect($review->lighting_rating)->toEqual(Rating::from(3));
});

it('rejects an out-of-range integer before it reaches the database', function (int $value): void {
    $review = Review::factory()->create();

    expect(fn () => $review->facilities_rating = $value)->toThrow(InvalidRatingException::class);
})->with([0, 6]);

it('builds CourtScores from the four cast ratings', function (): void {
    $review = Review::factory()->create([
        'glass_rating' => 5,
        'lighting_rating' => 4,
        'turf_rating' => 3,
        'facilities_rating' => 2,
    ]);

    $scores = Review::query()->whereKey($review->id)->firstOrFail()->scores();

    expect($scores)->toBeInstanceOf(CourtScores::class)
        ->and($scores->toArray())->toBe(['glass' => 5, 'lighting' => 4, 'turf' => 3, 'facilities' => 2])
        ->and($scores->overall())->toBe(3.5);
});
