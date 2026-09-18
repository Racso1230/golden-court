<?php

declare(strict_types=1);

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * @return array<string, array{0: string, 1: string}>
 */
function adminRoutes(): array
{
    $claim = VenueClaim::factory()->create();
    $review = Review::factory()->pending()->create();

    return [
        'GET admin.dashboard' => ['get', route('admin.dashboard')],
        'GET admin.claims.index' => ['get', route('admin.claims.index')],
        'POST admin.claims.approve' => ['post', route('admin.claims.approve', $claim)],
        'POST admin.claims.reject' => ['post', route('admin.claims.reject', $claim)],
        'GET admin.flags.index' => ['get', route('admin.flags.index')],
        'GET admin.reviews.pending' => ['get', route('admin.reviews.pending')],
        'GET admin.reviews.show' => ['get', route('admin.reviews.show', $review)],
        'POST admin.reviews.resolve-flags' => ['post', route('admin.reviews.resolve-flags', $review)],
        'POST admin.reviews.status' => ['post', route('admin.reviews.status', $review)],
    ];
}

it('returns 403 to players and venue owners on every admin route', function (): void {
    $player = User::factory()->player()->create();
    $owner = User::factory()->venueOwner()->create();

    foreach (adminRoutes() as $name => [$method, $url]) {
        actingAs($player)->{$method}($url)->assertForbidden();
        actingAs($owner)->{$method}($url)->assertForbidden();
    }
});

it('redirects guests to the login page on every admin route', function (): void {
    foreach (adminRoutes() as [$method, $url]) {
        if ($method === 'get') {
            get($url)->assertRedirect(route('login'));
        }
    }
});

it('lets an admin see the dashboard with counts', function (): void {
    VenueClaim::factory()->count(2)->create();
    Review::factory()->pending()->create();

    actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Admin/Dashboard')
            ->where('counts.pendingClaims', 2)
            ->where('counts.flaggedReviews', 0)
            ->where('counts.pendingReviews', 1));
});
