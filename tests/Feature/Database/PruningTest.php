<?php

declare(strict_types=1);

use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

it('prunes soft-deleted reviews older than the retention window, taking their votes with them', function (): void {
    config()->set('golden_court.retention.deleted_reviews_days', 90);
    $old = Review::factory()->create();
    ReviewVote::factory()->for($old)->create();
    $old->delete();
    Review::query()->withTrashed()->whereKey($old->id)->toBase()->update(['deleted_at' => now()->subDays(91)]);

    $recent = Review::factory()->create();
    $recent->delete();

    $live = Review::factory()->create();

    runArtisan('model:prune', ['--model' => [Review::class]])->assertSuccessful();

    expect(Review::withTrashed()->find($old->id))->toBeNull()
        ->and(DB::table('review_votes')->where('review_id', $old->id)->exists())->toBeFalse()
        ->and(Review::withTrashed()->find($recent->id))->not->toBeNull()
        ->and(Review::query()->find($live->id))->not->toBeNull();
});

it('prunes resolved flags older than the retention window only', function (): void {
    config()->set('golden_court.retention.resolved_flags_days', 180);
    $stale = ReviewFlag::factory()->resolved()->create(['resolved_at' => now()->subDays(181)]);
    $fresh = ReviewFlag::factory()->resolved()->create(['resolved_at' => now()->subDays(10)]);
    $open = ReviewFlag::factory()->create();

    runArtisan('model:prune', ['--model' => [ReviewFlag::class]])->assertSuccessful();

    expect(ReviewFlag::query()->find($stale->id))->toBeNull()
        ->and(ReviewFlag::query()->find($fresh->id))->not->toBeNull()
        ->and(ReviewFlag::query()->find($open->id))->not->toBeNull();
});

it('schedules the daily prune', function (): void {
    $events = collect(app(Schedule::class)->events())
        ->map(fn (Event $event): string => $event->command ?? '');

    expect($events->filter(fn (string $command): bool => str_contains($command, 'model:prune')))->toHaveCount(1);
});
