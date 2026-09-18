<?php

declare(strict_types=1);

use App\Domain\Users\Enums\Role;

it('has a human label for every role', function (): void {
    expect(Role::Player->label())->toBe('Player')
        ->and(Role::VenueOwner->label())->toBe('Venue owner')
        ->and(Role::Admin->label())->toBe('Admin');
});

it('is backed by the database values the schema will store', function (): void {
    expect(array_map(fn (Role $role): string => $role->value, Role::cases()))
        ->toBe(['player', 'venue_owner', 'admin']);
});
