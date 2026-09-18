<?php

declare(strict_types=1);

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use Illuminate\Support\Facades\Bus;

it('queues a court recalculation for every review event', function (object $event): void {
    Bus::fake();

    event($event);

    Bus::assertDispatched(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === 17);
})->with([
    'submitted' => fn (): ReviewSubmitted => new ReviewSubmitted(reviewId: 1, courtId: 17),
    'updated' => fn (): ReviewUpdated => new ReviewUpdated(reviewId: 1, courtId: 17),
    'deleted' => fn (): ReviewDeleted => new ReviewDeleted(courtId: 17),
    'status changed' => fn (): ReviewStatusChanged => new ReviewStatusChanged(1, 17, ReviewStatus::Pending, ReviewStatus::Published),
]);
