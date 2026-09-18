<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Statistics\CachedSiteStatistics;
use Illuminate\Console\Command;

/**
 * Re-queues a score recalculation for every court (and, through each court
 * job, its venue). Run it after changing the aggregation strategy or the
 * Bayesian confidence.
 */
class RecalculateScoresCommand extends Command
{
    protected $signature = 'golden-court:recalculate-scores
                            {--sync : Run the jobs immediately instead of queueing them}';

    protected $description = 'Recalculate every court and venue score with the configured aggregator';

    public function handle(): int
    {
        CachedSiteStatistics::forget();

        $count = 0;

        Court::query()->select('id')->orderBy('id')->each(function (Court $court) use (&$count): void {
            if ($this->option('sync') === true) {
                RecalculateCourtScore::dispatchSync($court->id);
            } else {
                RecalculateCourtScore::dispatch($court->id);
            }

            $count++;
        });

        $this->info(sprintf(
            '%s recalculation for %d %s using the "%s" strategy.',
            $this->option('sync') === true ? 'Ran' : 'Queued',
            $count,
            $count === 1 ? 'court' : 'courts',
            config()->string('golden_court.aggregation.strategy'),
        ));

        return self::SUCCESS;
    }
}
