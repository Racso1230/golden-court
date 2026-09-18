<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

it('rejects a second reply on the same review', function (): void {
    $reply = ReviewReply::factory()->create();

    expectUniqueViolation(fn () => ReviewReply::factory()->for($reply->review)->create());
});

it('rejects an empty body', function (): void {
    expectCheckViolation(fn () => DB::table('review_replies')->insert([
        'review_id' => Review::factory()->create()->id,
        'user_id' => User::factory()->create()->id,
        'body' => '',
    ]));
});

it('rejects a body longer than 1000 characters', function (): void {
    expectCheckViolation(fn () => DB::table('review_replies')->insert([
        'review_id' => Review::factory()->create()->id,
        'user_id' => User::factory()->create()->id,
        'body' => str_repeat('x', 1001),
    ]));
});

it('cascades deletion from the review', function (): void {
    $reply = ReviewReply::factory()->create();

    DB::table('reviews')->where('id', $reply->review_id)->delete();

    expect(DB::table('review_replies')->where('id', $reply->id)->exists())->toBeFalse();
});
