<?php

declare(strict_types=1);

use App\Domain\Reviews\Exceptions\InvalidRatingException;
use App\Domain\Reviews\ValueObjects\Rating;

it('accepts the lower and upper bounds', function (): void {
    expect(Rating::from(1)->value)->toBe(1)
        ->and(Rating::from(5)->value)->toBe(5)
        ->and(Rating::MIN)->toBe(1)
        ->and(Rating::MAX)->toBe(5);
});

it('rejects a rating below the minimum', function (): void {
    expect(fn (): Rating => Rating::from(0))
        ->toThrow(InvalidRatingException::class, 'between 1 and 5, 0 given');
});

it('rejects a rating above the maximum', function (): void {
    expect(fn (): Rating => Rating::from(6))
        ->toThrow(InvalidRatingException::class, 'between 1 and 5, 6 given');
});

it('compares by value', function (): void {
    expect(Rating::from(3)->equals(Rating::from(3)))->toBeTrue()
        ->and(Rating::from(3)->equals(Rating::from(4)))->toBeFalse();
});

it('serialises to a bare integer', function (): void {
    expect(json_encode(Rating::from(4)))->toBe('4')
        ->and(json_encode(['glass' => Rating::from(2)]))->toBe('{"glass":2}');
});
