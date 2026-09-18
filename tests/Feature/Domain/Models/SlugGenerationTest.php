<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;

it('generates a venue slug from its name', function (): void {
    $venue = Venue::factory()->create(['name' => 'Harbourside Padel Club']);

    expect($venue->slug)->toBe('harbourside-padel-club');
});

it('suffixes a venue slug that is already taken', function (): void {
    Venue::factory()->create(['name' => 'Riverside Padel']);
    $second = Venue::factory()->create(['name' => 'Riverside Padel']);
    $third = Venue::factory()->create(['name' => 'Riverside Padel']);

    expect($second->slug)->toBe('riverside-padel-2')
        ->and($third->slug)->toBe('riverside-padel-3');
});

it('keeps an explicitly provided venue slug', function (): void {
    $venue = Venue::factory()->create(['name' => 'Riverside Padel', 'slug' => 'custom-slug']);

    expect($venue->slug)->toBe('custom-slug');
});

it('generates a court slug unique within its venue only', function (): void {
    $venue = Venue::factory()->create();
    $first = Court::factory()->for($venue)->create(['name' => 'Court 1']);
    $second = Court::factory()->for($venue)->create(['name' => 'Court 1']);
    $elsewhere = Court::factory()->create(['name' => 'Court 1']);

    expect($first->slug)->toBe('court-1')
        ->and($second->slug)->toBe('court-1-2')
        ->and($elsewhere->slug)->toBe('court-1');
});
