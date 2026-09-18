<?php

declare(strict_types=1);

use App\Domain\Reviews\Exceptions\InvalidAggregateScoreException;
use App\Domain\Reviews\ValueObjects\AggregateScore;

it('represents the absence of reviews', function (): void {
    $empty = AggregateScore::empty();

    expect($empty->value)->toBe(0.0)
        ->and($empty->reviewCount)->toBe(0)
        ->and($empty->hasReviews())->toBeFalse();
});

it('accepts the score boundaries', function (): void {
    expect(AggregateScore::of(0.0, 1)->value)->toBe(0.0)
        ->and(AggregateScore::of(5.0, 1)->value)->toBe(5.0);
});

it('rejects a score below zero', function (): void {
    expect(fn (): AggregateScore => AggregateScore::of(-0.1, 1))
        ->toThrow(InvalidAggregateScoreException::class);
});

it('rejects a score above five', function (): void {
    expect(fn (): AggregateScore => AggregateScore::of(5.1, 1))
        ->toThrow(InvalidAggregateScoreException::class);
});

it('rejects a negative review count', function (): void {
    expect(fn (): AggregateScore => AggregateScore::of(3.0, -1))
        ->toThrow(InvalidAggregateScoreException::class, 'cannot be negative');
});

it('rejects a non-zero score with no reviews', function (): void {
    expect(fn (): AggregateScore => AggregateScore::of(3.0, 0))
        ->toThrow(InvalidAggregateScoreException::class, 'zero reviews');
});

it('treats a zero score with no reviews as empty', function (): void {
    expect(AggregateScore::of(0.0, 0)->equals(AggregateScore::empty()))->toBeTrue();
});

it('keeps the value to one decimal place', function (): void {
    expect(AggregateScore::of(3.26, 4)->value)->toBe(3.3)
        ->and(AggregateScore::of(3.24, 4)->value)->toBe(3.2);
});

it('knows when it was computed from reviews', function (): void {
    expect(AggregateScore::of(4.2, 7)->hasReviews())->toBeTrue()
        ->and(AggregateScore::of(4.2, 7)->reviewCount)->toBe(7);
});

it('serialises value and review count', function (): void {
    expect(json_encode(AggregateScore::of(4.2, 7)))->toBe('{"value":4.2,"review_count":7}');
});
