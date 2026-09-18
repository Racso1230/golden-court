<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Model::shouldBeStrict() makes any lazy load throw, so a 200 already proves
 * there is no N+1. The query counts pin the cost so it cannot creep up.
 */
it('renders a full page of venues in a fixed number of queries', function (): void {
    Venue::factory()->count(20)->create()->each(function (Venue $venue): void {
        Court::factory()->count(2)->for($venue)->create();
    });

    DB::enableQueryLog();
    get(route('venues.index'))->assertOk();

    // venues count, venues page (courts_count is a subselect), golden courts
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(4);
});

it('renders a venue with six courts in a fixed number of queries', function (): void {
    $venue = Venue::factory()->claimedBy(User::factory()->venueOwner()->create())->create();
    Court::factory()->count(6)->for($venue)->create();

    DB::enableQueryLog();
    get(route('venues.show', $venue))->assertOk();

    // venue binding, courts, owner, golden court
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(5);
});

it('renders a court with a page of reviews and replies in a fixed number of queries', function (): void {
    $court = Court::factory()->create();
    Review::factory()->count(20)->for($court)->create()->each(function (Review $review): void {
        ReviewReply::factory()->for($review)->create();
    });
    $viewer = User::factory()->player()->create();

    DB::enableQueryLog();
    actingAs($viewer)->get(route('courts.show', [$court->venue, $court]))->assertOk();

    // venue + court bindings, viewer, review count + page, users, replies, reply users, votes, averages, golden court, policy pre-check
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(13);
});
