<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Listeners;

use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;

/**
 * Any change to a review, whatever its kind, means the court's score is stale.
 */
final class QueueScoreRecalculation
{
    public function handle(ReviewSubmitted|ReviewUpdated|ReviewDeleted|ReviewStatusChanged $event): void
    {
        RecalculateCourtScore::dispatch($event->courtId);
    }
}
