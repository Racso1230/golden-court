<?php

declare(strict_types=1);

use App\Domain\Moderation\Enums\FlagReason;

it('labels every flag reason', function (): void {
    expect(FlagReason::Spam->label())->toBe('Spam')
        ->and(FlagReason::Offensive->label())->toBe('Offensive')
        ->and(FlagReason::NotAReview->label())->toBe('Not a review')
        ->and(FlagReason::ConflictOfInterest->label())->toBe('Conflict of interest')
        ->and(FlagReason::Other->label())->toBe('Other');
});

it('uses snake_case backing values', function (): void {
    expect(FlagReason::NotAReview->value)->toBe('not_a_review')
        ->and(FlagReason::ConflictOfInterest->value)->toBe('conflict_of_interest');
});
