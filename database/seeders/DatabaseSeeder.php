<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\ValueObjects\CourtScores;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private const int MIN_REVIEWS = 150;

    private const int MAX_REVIEWS = 300;

    /**
     * How many reviews a court gets is drawn from this list, so a few courts
     * are busy, most are quiet and some have none at all.
     *
     * @var list<int>
     */
    private const array REVIEW_COUNT_WEIGHTS = [0, 0, 0, 1, 1, 2, 3, 4, 6, 8, 10, 14, 18, 24];

    /**
     * Fictional venues. None of these are real businesses.
     *
     * @var list<array{name: string, city: string, latitude: float, longitude: float, postcode: string}>
     */
    private const array VENUES = [
        ['name' => 'Riverside Padel Club', 'city' => 'London', 'latitude' => 51.4832, 'longitude' => -0.2210, 'postcode' => 'SW13 9RU'],
        ['name' => 'Camden Lock Padel', 'city' => 'London', 'latitude' => 51.5413, 'longitude' => -0.1462, 'postcode' => 'NW1 8AF'],
        ['name' => 'Northern Quarter Padel', 'city' => 'Manchester', 'latitude' => 53.4839, 'longitude' => -2.2364, 'postcode' => 'M4 1LE'],
        ['name' => 'Salford Quays Racket Centre', 'city' => 'Manchester', 'latitude' => 53.4717, 'longitude' => -2.2955, 'postcode' => 'M50 3AZ'],
        ['name' => 'Jewellery Quarter Padel', 'city' => 'Birmingham', 'latitude' => 52.4884, 'longitude' => -1.9107, 'postcode' => 'B18 6HA'],
        ['name' => 'Headingley Padel Arena', 'city' => 'Leeds', 'latitude' => 53.8173, 'longitude' => -1.5828, 'postcode' => 'LS6 3BR'],
        ['name' => 'Harbourside Padel', 'city' => 'Bristol', 'latitude' => 51.4489, 'longitude' => -2.6023, 'postcode' => 'BS1 5UH'],
        ['name' => 'Leith Shore Padel Club', 'city' => 'Edinburgh', 'latitude' => 55.9760, 'longitude' => -3.1700, 'postcode' => 'EH6 6QU'],
        ['name' => 'Clydeside Padel', 'city' => 'Glasgow', 'latitude' => 55.8590, 'longitude' => -4.2860, 'postcode' => 'G3 8QQ'],
        ['name' => 'Baltic Triangle Padel', 'city' => 'Liverpool', 'latitude' => 53.3970, 'longitude' => -2.9800, 'postcode' => 'L1 0AH'],
        ['name' => 'Quayside Padel Hub', 'city' => 'Newcastle upon Tyne', 'latitude' => 54.9690, 'longitude' => -1.6000, 'postcode' => 'NE1 3DX'],
        ['name' => 'Bay Padel Cardiff', 'city' => 'Cardiff', 'latitude' => 51.4640, 'longitude' => -3.1650, 'postcode' => 'CF10 4PA'],
    ];

    public function __construct(private readonly RatingAggregator $aggregator) {}

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Ada Admin',
            'display_name' => 'ada',
            'email' => 'admin@goldencourt.test',
        ]);

        $owner = User::factory()->venueOwner()->create([
            'name' => 'Owen Owner',
            'display_name' => 'owen',
            'email' => 'owner@goldencourt.test',
        ]);

        $players = User::factory()->player()->count(30)->create();

        $venues = $this->seedVenues($owner);

        $this->seedReviews(Court::query()->get(), $players);

        // TODO (Phase 4): replace with the RecalculateAggregates job run synchronously.
        $this->recalculateAggregates($venues);
    }

    /**
     * @return Collection<int, Venue>
     */
    private function seedVenues(User $owner): Collection
    {
        $venues = new Collection;

        foreach (self::VENUES as $index => $attributes) {
            $factory = Venue::factory();

            // The venue owner account owns the first venue so the claim flows have data.
            if ($index === 0) {
                $factory = $factory->claimedBy($owner);
            }

            $venue = $factory->create($attributes);

            Court::factory()
                ->count(fake()->numberBetween(2, 6))
                ->for($venue)
                ->sequence(fn (Sequence $sequence): array => ['name' => sprintf('Court %d', $sequence->index + 1)])
                ->create();

            $venues->push($venue->load('courts'));
        }

        return $venues;
    }

    /**
     * @param  Collection<int, Court>  $courts
     * @param  Collection<int, User>  $players
     */
    private function seedReviews(Collection $courts, Collection $players): void
    {
        $counts = $this->reviewCountsFor($courts->count(), $players->count());

        foreach ($courts->values() as $index => $court) {
            $count = $counts[$index] ?? 0;

            if ($count === 0) {
                continue;
            }

            // One review per player per court, so pick distinct players.
            foreach ($players->random($count) as $player) {
                Review::factory()->for($player)->for($court)->create();
            }
        }
    }

    /**
     * Draw a review count per court, then nudge the total into range.
     *
     * @return array<int, int>
     */
    private function reviewCountsFor(int $courtCount, int $playerCount): array
    {
        $counts = [];

        $lastWeight = count(self::REVIEW_COUNT_WEIGHTS) - 1;

        for ($i = 0; $i < $courtCount; $i++) {
            $counts[] = min($playerCount, self::REVIEW_COUNT_WEIGHTS[fake()->numberBetween(0, $lastWeight)]);
        }

        while (array_sum($counts) < self::MIN_REVIEWS) {
            $index = array_rand($counts);
            $counts[$index] = min($playerCount, $counts[$index] + 1);
        }

        while (array_sum($counts) > self::MAX_REVIEWS) {
            $index = array_rand($counts);
            $counts[$index] = max(0, $counts[$index] - 1);
        }

        return $counts;
    }

    /**
     * @param  Collection<int, Venue>  $venues
     */
    private function recalculateAggregates(Collection $venues): void
    {
        foreach ($venues as $venue) {
            $venueScores = [];

            foreach ($venue->courts as $court) {
                $courtScores = $court->publishedReviews()
                    ->get()
                    ->map(fn (Review $review): CourtScores => $review->scores())
                    ->all();

                $aggregate = $this->aggregator->aggregate($courtScores);

                $court->forceFill([
                    'aggregate_score' => $aggregate->value,
                    'review_count' => $aggregate->reviewCount,
                ])->save();

                $venueScores = [...$venueScores, ...$courtScores];
            }

            $aggregate = $this->aggregator->aggregate($venueScores);

            $venue->forceFill([
                'aggregate_score' => $aggregate->value,
                'review_count' => $aggregate->reviewCount,
            ])->save();
        }
    }
}
