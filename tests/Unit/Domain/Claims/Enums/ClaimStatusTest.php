<?php

declare(strict_types=1);

use App\Domain\Claims\Enums\ClaimStatus;

it('labels every claim status', function (): void {
    expect(ClaimStatus::Pending->label())->toBe('Pending')
        ->and(ClaimStatus::Approved->label())->toBe('Approved')
        ->and(ClaimStatus::Rejected->label())->toBe('Rejected');
});

it('round-trips through its backing value', function (): void {
    foreach (ClaimStatus::cases() as $status) {
        expect(ClaimStatus::from($status->value))->toBe($status);
    }
});

it('lets a pending claim be approved or rejected, once', function (): void {
    expect(ClaimStatus::Pending->canTransitionTo(ClaimStatus::Approved))->toBeTrue()
        ->and(ClaimStatus::Pending->canTransitionTo(ClaimStatus::Rejected))->toBeTrue()
        ->and(ClaimStatus::Pending->canTransitionTo(ClaimStatus::Pending))->toBeFalse()
        ->and(ClaimStatus::Approved->allowedTransitions())->toBe([])
        ->and(ClaimStatus::Rejected->allowedTransitions())->toBe([]);
});

it('knows when it is settled', function (): void {
    expect(ClaimStatus::Pending->isSettled())->toBeFalse()
        ->and(ClaimStatus::Approved->isSettled())->toBeTrue()
        ->and(ClaimStatus::Rejected->isSettled())->toBeTrue();
});
