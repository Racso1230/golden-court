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
