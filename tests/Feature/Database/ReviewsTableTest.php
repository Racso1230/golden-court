<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A raw row that satisfies every constraint, bypassing model casts so the
 * database itself is what rejects bad values.
 *
 * @return array<string, mixed>
 */
function validReviewRow(): array
{
    return [
        'user_id' => User::factory()->create()->id,
        'court_id' => Court::factory()->create()->id,
        'glass_rating' => 4,
        'lighting_rating' => 4,
        'turf_rating' => 4,
        'facilities_rating' => 4,
        'body' => str_repeat('Solid court. ', 5),
        'status' => 'published',
    ];
}

it('rejects a second review from the same user on the same court', function (): void {
    $review = Review::factory()->create();

    expectUniqueViolation(fn () => Review::factory()->for($review->user)->for($review->court)->create());
});

it('allows the same user to review different courts', function (): void {
    $user = User::factory()->create();

    Review::factory()->for($user)->count(2)->create();

    expect(Review::query()->where('user_id', $user->id)->count())->toBe(2);
});

it('rejects a rating outside 1..5', function (string $column, int $value): void {
    expectCheckViolation(fn () => DB::table('reviews')->insert([...validReviewRow(), $column => $value]));
})->with([
    ['glass_rating', 0],
    ['glass_rating', 6],
    ['lighting_rating', 0],
    ['lighting_rating', 6],
    ['turf_rating', 0],
    ['turf_rating', 6],
    ['facilities_rating', 0],
    ['facilities_rating', 6],
]);

it('accepts ratings at the boundaries', function (): void {
    DB::table('reviews')->insert([...validReviewRow(), 'glass_rating' => 1, 'facilities_rating' => 5]);

    expect(DB::table('reviews')->count())->toBe(1);
});

it('rejects a body shorter than 20 characters', function (): void {
    expectCheckViolation(fn () => DB::table('reviews')->insert([...validReviewRow(), 'body' => str_repeat('x', 19)]));
});

it('rejects a body longer than 2000 characters', function (): void {
    expectCheckViolation(fn () => DB::table('reviews')->insert([...validReviewRow(), 'body' => str_repeat('x', 2001)]));
});

it('accepts a body at the length boundaries', function (int $length): void {
    DB::table('reviews')->insert([...validReviewRow(), 'body' => str_repeat('x', $length)]);

    expect(DB::table('reviews')->count())->toBe(1);
})->with([20, 2000]);

it('rejects a status outside the enum', function (): void {
    expectCheckViolation(fn () => DB::table('reviews')->insert([...validReviewRow(), 'status' => 'archived']));
});

it('defaults status to pending', function (): void {
    $row = validReviewRow();
    unset($row['status']);

    $id = DB::table('reviews')->insertGetId($row);

    expect(DB::table('reviews')->where('id', $id)->value('status'))->toBe('pending');
});
