<?php

declare(strict_types=1);

namespace App\Domain\Courts\Queries;

use App\Domain\Courts\Models\Court;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The "Golden Court" of a city: the top-scoring court with enough reviews
 * to be trusted. Results are cached per city and busted whenever a court's
 * score is recalculated.
 */
final class GoldenCourtQuery
{
    public const int MIN_REVIEWS = 5;

    private const int TTL_SECONDS = 600;

    public function forCity(string $city): ?Court
    {
        return $this->forCities([$city])->get(self::cityKey($city));
    }

    /**
     * One query for many cities, keyed by the normalised city name. Cities
     * without a qualifying court are cached too, so they cost nothing next time.
     *
     * @param  array<int, string>  $cities
     * @return Collection<string, Court>
     */
    public function forCities(array $cities): Collection
    {
        /** @var Collection<string, Court> $result */
        $result = new Collection;
        $missing = [];

        foreach (array_unique(array_map(self::cityKey(...), $cities)) as $cityKey) {
            $cached = Cache::get(self::cacheKey($cityKey));

            if (! is_array($cached)) {
                $missing[] = $cityKey;

                continue;
            }

            if ($cached['court'] instanceof Court) {
                $result->put($cityKey, $cached['court']);
            }
        }

        if ($missing === []) {
            return $result;
        }

        $found = Court::query()
            ->selectRaw('DISTINCT ON (lower(venues.city)) courts.*, lower(venues.city) AS city_key')
            ->join('venues', 'venues.id', '=', 'courts.venue_id')
            ->whereNull('venues.deleted_at')
            ->whereIn(DB::raw('lower(venues.city)'), $missing)
            ->where('courts.review_count', '>=', self::MIN_REVIEWS)
            ->orderByRaw('lower(venues.city), courts.aggregate_score DESC, courts.review_count DESC, courts.id')
            ->get()
            ->keyBy('city_key');

        foreach ($missing as $cityKey) {
            $court = $found->get($cityKey);

            Cache::put(self::cacheKey($cityKey), ['court' => $court], self::TTL_SECONDS);

            if ($court !== null) {
                $result->put($cityKey, $court);
            }
        }

        return $result;
    }

    public static function forget(string $city): void
    {
        Cache::forget(self::cacheKey(self::cityKey($city)));
    }

    public static function cityKey(string $city): string
    {
        return mb_strtolower(trim($city));
    }

    private static function cacheKey(string $cityKey): string
    {
        return 'golden-court:'.md5($cityKey);
    }
}
