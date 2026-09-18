<?php

declare(strict_types=1);

use App\Domain\Moderation\Models\ReviewFlag;
use Illuminate\Support\Facades\DB;

it('rejects a second flag from the same user on the same review', function (): void {
    $flag = ReviewFlag::factory()->create();

    expectUniqueViolation(fn () => ReviewFlag::factory()->for($flag->review)->for($flag->user)->create());
});

it('rejects a reason outside the enum', function (): void {
    $flag = ReviewFlag::factory()->create();

    expectCheckViolation(fn () => DB::table('review_flags')->where('id', $flag->id)->update(['reason' => 'disliked_it']));
});

it('keeps the flag but clears the resolver when the resolving admin is deleted', function (): void {
    $flag = ReviewFlag::factory()->resolved()->create();

    DB::table('users')->where('id', $flag->resolved_by_user_id)->delete();

    expect($flag->refresh()->resolved_by_user_id)->toBeNull()
        ->and($flag->isResolved())->toBeTrue();
});
