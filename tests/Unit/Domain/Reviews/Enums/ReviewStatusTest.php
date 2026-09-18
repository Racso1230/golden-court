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

it('allows the moderation transitions', function (ReviewStatus $from, ReviewStatus $to): void {
    expect($from->canTransitionTo($to))->toBeTrue();
})->with([
    'pending to published' => [ReviewStatus::Pending, ReviewStatus::Published],
    'pending to removed' => [ReviewStatus::Pending, ReviewStatus::Removed],
    'published to flagged' => [ReviewStatus::Published, ReviewStatus::Flagged],
    'published to removed (admin takedown)' => [ReviewStatus::Published, ReviewStatus::Removed],
    'flagged to published' => [ReviewStatus::Flagged, ReviewStatus::Published],
    'flagged to removed' => [ReviewStatus::Flagged, ReviewStatus::Removed],
]);

it('forbids every other transition', function (ReviewStatus $from, ReviewStatus $to): void {
    expect($from->canTransitionTo($to))->toBeFalse();
})->with([
    'pending to flagged' => [ReviewStatus::Pending, ReviewStatus::Flagged],
    'pending to pending' => [ReviewStatus::Pending, ReviewStatus::Pending],
    'published to pending' => [ReviewStatus::Published, ReviewStatus::Pending],
    'published to published' => [ReviewStatus::Published, ReviewStatus::Published],
    'flagged to pending' => [ReviewStatus::Flagged, ReviewStatus::Pending],
    'flagged to flagged' => [ReviewStatus::Flagged, ReviewStatus::Flagged],
    'removed to published' => [ReviewStatus::Removed, ReviewStatus::Published],
    'removed to pending' => [ReviewStatus::Removed, ReviewStatus::Pending],
]);

it('is terminal once removed', function (): void {
    expect(ReviewStatus::Removed->allowedTransitions())->toBe([]);
});
