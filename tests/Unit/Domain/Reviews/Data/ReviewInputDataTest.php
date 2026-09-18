<?php

declare(strict_types=1);

use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\Data\UpdateReviewData;
use App\Domain\Reviews\Exceptions\InvalidRatingException;
use Carbon\CarbonImmutable;

it('builds CourtScores from the submitted ratings', function (): void {
    $data = new SubmitReviewData(
        courtId: 7,
        glass: 5,
        lighting: 4,
        turf: 3,
        facilities: 2,
        body: 'Great glass, decent lights, tired turf.',
        playedOn: CarbonImmutable::parse('2026-09-01'),
    );

    expect($data->scores()->toArray())->toBe(['glass' => 5, 'lighting' => 4, 'turf' => 3, 'facilities' => 2])
        ->and($data->scores()->overall())->toBe(3.5)
        ->and($data->playedOn?->toDateString())->toBe('2026-09-01');
});

it('refuses to build scores from an out-of-range rating', function (): void {
    $data = new UpdateReviewData(glass: 6, lighting: 4, turf: 3, facilities: 2, body: 'Twenty characters!!');

    expect(fn () => $data->scores())->toThrow(InvalidRatingException::class);
});

it('defaults played on to null', function (): void {
    $data = new UpdateReviewData(glass: 1, lighting: 1, turf: 1, facilities: 1, body: 'Twenty characters!!');

    expect($data->playedOn)->toBeNull();
});
