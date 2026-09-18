<?php

declare(strict_types=1);

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;

it('rejects a second pending claim on the same venue', function (): void {
    $claim = VenueClaim::factory()->create();

    expectUniqueViolation(fn () => VenueClaim::factory()->for($claim->venue)->create());
});

it('allows a pending claim alongside settled claims on the same venue', function (): void {
    $venue = Venue::factory()->create();

    VenueClaim::factory()->for($venue)->approved()->create();
    VenueClaim::factory()->for($venue)->rejected()->create();
    VenueClaim::factory()->for($venue)->create();

    expect(VenueClaim::query()->where('venue_id', $venue->id)->count())->toBe(3)
        ->and(VenueClaim::query()->where('venue_id', $venue->id)->where('status', ClaimStatus::Pending)->count())->toBe(1);
});

it('allows pending claims on different venues', function (): void {
    VenueClaim::factory()->count(2)->create();

    expect(VenueClaim::query()->where('status', ClaimStatus::Pending)->count())->toBe(2);
});

it('rejects a status outside the enum', function (): void {
    $claim = VenueClaim::factory()->create();

    expectCheckViolation(fn () => DB::table('venue_claims')->where('id', $claim->id)->update(['status' => 'withdrawn']));
});
