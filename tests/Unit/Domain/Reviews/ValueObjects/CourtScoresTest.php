<?php

declare(strict_types=1);

use App\Domain\Reviews\Exceptions\InvalidRatingException;
use App\Domain\Reviews\ValueObjects\CourtScores;
use App\Domain\Reviews\ValueObjects\Rating;

it('builds from four integers', function (): void {
    $scores = CourtScores::fromInts(5, 4, 3, 2);

    expect($scores->glass)->toEqual(Rating::from(5))
        ->and($scores->lighting)->toEqual(Rating::from(4))
        ->and($scores->turf)->toEqual(Rating::from(3))
        ->and($scores->facilities)->toEqual(Rating::from(2));
});

it('rejects an out-of-range dimension', function (): void {
    expect(fn (): CourtScores => CourtScores::fromInts(5, 4, 3, 0))
        ->toThrow(InvalidRatingException::class);
});

it('computes the overall as the mean of the four ratings', function (): void {
    expect(CourtScores::fromInts(5, 4, 3, 2)->overall())->toBe(3.5)
        ->and(CourtScores::fromInts(4, 4, 4, 4)->overall())->toBe(4.0)
        ->and(CourtScores::fromInts(1, 1, 1, 1)->overall())->toBe(1.0);
});

it('rounds the overall to one decimal place', function (): void {
    // (5 + 4 + 4 + 4) / 4 = 4.25 -> 4.3 (half away from zero)
    expect(CourtScores::fromInts(5, 4, 4, 4)->overall())->toBe(4.3)
        // (5 + 5 + 5 + 4) / 4 = 4.75 -> 4.8
        ->and(CourtScores::fromInts(5, 5, 5, 4)->overall())->toBe(4.8);
});

it('exposes a fixed-shape array', function (): void {
    expect(CourtScores::fromInts(5, 4, 3, 2)->toArray())->toBe([
        'glass' => 5,
        'lighting' => 4,
        'turf' => 3,
        'facilities' => 2,
    ]);
});

it('serialises to the same shape as toArray', function (): void {
    expect(json_encode(CourtScores::fromInts(5, 4, 3, 2)))
        ->toBe('{"glass":5,"lighting":4,"turf":3,"facilities":2}');
});
