<?php

declare(strict_types=1);

use App\Domain\Users\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// Array props reach a `where` closure wrapped in a Collection.

it('hides private pages from crawlers and names them', function (): void {
    actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'title') === sprintf('<title data-inertia="title">Dashboard | %s</title>', config('app.name'))
                && headTag($head->all(), 'robots') === '<meta name="robots" content="noindex, nofollow" data-inertia="robots">'
                && headTag($head->all(), 'canonical') === null
                && headTag($head->all(), 'og:title') === null));
});

it('treats the auth pages as private', function (): void {
    get(route('login'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'title') === sprintf('<title data-inertia="title">Log in | %s</title>', config('app.name'))
                && headTag($head->all(), 'robots') === '<meta name="robots" content="noindex, nofollow" data-inertia="robots">'));
});

it('gives a public page a canonical, a description and sharing tags by default', function (): void {
    // A route of its own: every real public page builds richer metadata in its controller.
    Route::middleware('web')->get('/seo-default', fn (): Response => Inertia::render('Home', ['topVenues' => [], 'recentReviews' => []]));
    $base = rtrim((string) config('app.url'), '/');

    get('/seo-default')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'title') === sprintf('<title data-inertia="title">%s</title>', config('app.name'))
                && headTag($head->all(), 'robots') === '<meta name="robots" content="index, follow" data-inertia="robots">'
                && headTag($head->all(), 'canonical') === sprintf('<link rel="canonical" href="%s/seo-default" data-inertia="canonical">', $base)
                && headTag($head->all(), 'description') !== null
                && headTag($head->all(), 'og:url') !== null));
});

it('prints the same head tags from the root view when SSR is off', function (): void {
    get(route('login'))
        ->assertOk()
        ->assertSee('<html lang="en-GB">', false)
        ->assertSee('<meta name="theme-color" content="#ffffff">', false)
        ->assertSee(sprintf('<title data-inertia="title">Log in | %s</title>', config('app.name')), false)
        ->assertSee('<meta name="robots" content="noindex, nofollow" data-inertia="robots">', false);
});
