<?php

declare(strict_types=1);

use App\Domain\Reviews\Policies\MinimumAccountAge;
use App\Domain\Users\Models\User;

it('says when a new account may review', function (): void {
    config()->set('golden_court.reviews.min_account_age_hours', 2);
    $user = User::factory()->create(['created_at' => now()->subMinutes(30)]);
    $rule = app(MinimumAccountAge::class);

    expect($rule->isMet($user))->toBeFalse()
        ->and($rule->reviewableFrom($user)?->toIso8601String())->toBe($user->created_at?->addHours(2)->toIso8601String());
});

it('is met once the account is old enough or when the rule is off', function (): void {
    config()->set('golden_court.reviews.min_account_age_hours', 1);
    $old = User::factory()->create(['created_at' => now()->subHours(1)->subMinute()]);
    $new = User::factory()->create(['created_at' => now()]);

    expect(app(MinimumAccountAge::class)->isMet($old))->toBeTrue();

    config()->set('golden_court.reviews.min_account_age_hours', 0);

    expect(app(MinimumAccountAge::class)->reviewableFrom($new))->toBeNull();
});
