<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Actions\SubmitReviewAction;
use App\Domain\Reviews\Actions\UpdateReviewAction;
use App\Domain\Reviews\ContentRules\BasicHeuristicsRule;
use App\Domain\Reviews\Contracts\ReviewContentRule;
use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\Data\UpdateReviewData;
use App\Domain\Reviews\Exceptions\ReviewContentRejectedException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travel;

it('binds the basic heuristics as the content rule', function (): void {
    expect(app(ReviewContentRule::class))->toBeInstanceOf(BasicHeuristicsRule::class);
});

it('rejects junk bodies on submission before touching the database', function (): void {
    $user = User::factory()->player()->create();
    $court = Court::factory()->create();

    expect(fn () => app(SubmitReviewAction::class)->handle($user, new SubmitReviewData($court->id, 5, 5, 5, 5, str_repeat('x', 40))))
        ->toThrow(ReviewContentRejectedException::class);

    expect(Review::query()->count())->toBe(0);
});

it('rejects junk bodies on update', function (): void {
    $review = Review::factory()->create();

    expect(fn () => app(UpdateReviewAction::class)->handle($review, new UpdateReviewData(3, 3, 3, 3, 'https://spam.example/a https://spam.example/b hi')))
        ->toThrow(ReviewContentRejectedException::class);
});

it('reports a rejected body as a validation error on the form', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.store'), [
            'court_id' => $court->id,
            'glass' => 4,
            'lighting' => 4,
            'turf' => 4,
            'facilities' => 4,
            'body' => str_repeat('z', 50),
        ])
        ->assertSessionHasErrors(['body' => 'Please write a few words about the court.']);
});

it('stops brand-new accounts from reviewing until they are old enough', function (): void {
    config()->set('golden_court.reviews.min_account_age_hours', 1);
    $court = Court::factory()->create();
    $newcomer = User::factory()->player()->justRegistered()->create();
    $established = User::factory()->player()->create();

    expect($newcomer->can('create', [Review::class, $court]))->toBeFalse()
        ->and($established->can('create', [Review::class, $court]))->toBeTrue();

    travel(2)->hours();

    expect($newcomer->can('create', [Review::class, $court]))->toBeTrue();
});

it('disables the minimum age when set to zero', function (): void {
    config()->set('golden_court.reviews.min_account_age_hours', 0);

    expect(User::factory()->player()->justRegistered()->create()->can('create', [Review::class, Court::factory()->create()]))->toBeTrue();
});

it('stops unverified users voting or flagging', function (): void {
    $review = Review::factory()->create();
    $unverified = User::factory()->player()->unverified()->create();

    expect($unverified->can('vote', $review))->toBeFalse()
        ->and($unverified->can('flag', $review))->toBeFalse();
});
