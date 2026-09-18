<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('defines every named limiter', function (string $name): void {
    expect(RateLimiter::limiter($name))->not->toBeNull();
})->with(['reviews', 'flags', 'claims', 'votes', 'search']);

it('applies the named limiters to their routes', function (string $routeName, string $limiter): void {
    $route = app('router')->getRoutes()->getByName($routeName);

    expect($route)->not->toBeNull()
        ->and($route?->gatherMiddleware())->toContain('throttle:'.$limiter);
})->with([
    ['reviews.store', 'reviews'],
    ['reviews.flags.store', 'flags'],
    ['reviews.vote', 'votes'],
    ['venues.claims.store', 'claims'],
    ['venues.index', 'search'],
]);

it('stops a user after three venue claims in a day', function (): void {
    $user = User::factory()->player()->create();

    foreach (Venue::factory()->count(3)->create() as $venue) {
        actingAs($user)
            ->post(route('venues.claims.store', $venue), ['evidence' => 'I manage this venue; my email matches the club domain.'])
            ->assertRedirect();
    }

    actingAs($user)
        ->post(route('venues.claims.store', Venue::factory()->create()), ['evidence' => 'I manage this venue; my email matches the club domain.'])
        ->assertTooManyRequests();
});

it('stops a user after sixty helpful votes in a minute', function (): void {
    $voter = User::factory()->player()->create();
    $review = Review::factory()->create();

    foreach (range(1, 60) as $attempt) {
        actingAs($voter)->post(route('reviews.vote', $review))->assertRedirect();
    }

    actingAs($voter)->post(route('reviews.vote', $review))->assertTooManyRequests();
});

it('limits search by IP and leaves other public pages alone', function (): void {
    foreach (range(1, 60) as $attempt) {
        get(route('venues.index'))->assertOk();
    }

    get(route('venues.index'))->assertTooManyRequests();
    get(route('home'))->assertOk();
});
