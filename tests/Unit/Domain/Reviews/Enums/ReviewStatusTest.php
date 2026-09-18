<?php

declare(strict_types=1);

use App\Domain\Reviews\Enums\ReviewStatus;

it('labels every review status', function (): void {
    expect(ReviewStatus::Pending->label())->toBe('Pending')
        ->and(ReviewStatus::Published->label())->toBe('Published')
        ->and(ReviewStatus::Flagged->label())->toBe('Flagged')
        ->and(ReviewStatus::Removed->label())->toBe('Removed');
});

it('treats only published reviews as visible', function (): void {
    expect(ReviewStatus::Published->isVisible())->toBeTrue()
        ->and(ReviewStatus::Pending->isVisible())->toBeFalse()
        ->and(ReviewStatus::Flagged->isVisible())->toBeFalse()
        ->and(ReviewStatus::Removed->isVisible())->toBeFalse();
});
