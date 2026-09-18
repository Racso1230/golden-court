<?php

declare(strict_types=1);

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

it('lists pending claims oldest first', function (): void {
    $older = VenueClaim::factory()->create(['created_at' => '2026-09-01 10:00:00']);
    $newer = VenueClaim::factory()->create(['created_at' => '2026-09-10 10:00:00']);
    VenueClaim::factory()->approved()->create();

    actingAs(User::factory()->admin()->create())
        ->get(route('admin.claims.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Admin/Claims/Index')
            ->has('claims.data', 2)
            ->where('claims.data.0.id', $older->id)
            ->where('claims.data.1.id', $newer->id)
            ->where('claims.data.0.venueName', $older->venue->name)
            ->where('claims.data.0.claimantDisplayName', $older->user->display_name));
});

it('approves a claim end to end', function (): void {
    $claim = VenueClaim::factory()->for(User::factory()->player())->create();

    actingAs(User::factory()->admin()->create())
        ->from(route('admin.claims.index'))
        ->post(route('admin.claims.approve', $claim))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.claims.index'));

    expect($claim->refresh()->status)->toBe(ClaimStatus::Approved)
        ->and($claim->venue->refresh()->claimed_by_user_id)->toBe($claim->user_id)
        ->and($claim->user->refresh()->role)->toBe(Role::VenueOwner);
});

it('rejects a claim with a reason', function (): void {
    $claim = VenueClaim::factory()->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.claims.reject', $claim), ['rejection_reason' => 'Could not verify ownership.'])
        ->assertSessionHasNoErrors();

    expect($claim->refresh()->status)->toBe(ClaimStatus::Rejected)
        ->and($claim->rejection_reason)->toBe('Could not verify ownership.')
        ->and($claim->venue->refresh()->isClaimed())->toBeFalse();
});

it('requires a rejection reason', function (): void {
    $claim = VenueClaim::factory()->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.claims.reject', $claim), [])
        ->assertSessionHasErrors('rejection_reason');

    expect($claim->refresh()->status)->toBe(ClaimStatus::Pending);
});

it('reports deciding a settled claim as a validation error', function (): void {
    $claim = VenueClaim::factory()->rejected()->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.claims.approve', $claim))
        ->assertSessionHasErrors('claim');
});
