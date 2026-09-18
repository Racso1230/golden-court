<?php

declare(strict_types=1);

namespace App\Domain\Venues\Queries;

use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Data\VenueSearchCriteria;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Composes the venue search from a criteria object. Full-text matching uses
 * the generated `search_vector` column and its GIN index; proximity uses the
 * earthdistance extension and the GiST index on `ll_to_earth(lat, lng)`.
 */
final class VenueSearchQuery
{
    private const string DISTANCE_SQL = 'earth_distance(ll_to_earth(?, ?), ll_to_earth(venues.latitude::float8, venues.longitude::float8))';

    private const string TS_QUERY_SQL = "plainto_tsquery('english', ?)";

    public function __construct(private readonly VenueSearchCriteria $criteria) {}

    /**
     * @return LengthAwarePaginator<int, Venue>
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->toBuilder()->paginate($perPage, page: $this->criteria->page);
    }

    /**
     * @return Builder<Venue>
     */
    public function toBuilder(): Builder
    {
        $query = Venue::query()
            ->select('venues.*')
            ->withCount('courts');

        $this->applyTerm($query);
        $this->applyCity($query);
        $this->applyProximity($query);
        $this->applyCourtFilters($query);
        $this->applyMinScore($query);
        $this->applySort($query);

        return $query;
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applyTerm(Builder $query): void
    {
        if ($this->criteria->term === null) {
            return;
        }

        $query->whereRaw('venues.search_vector @@ '.self::TS_QUERY_SQL, [$this->criteria->term]);
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applyCity(Builder $query): void
    {
        if ($this->criteria->city === null) {
            return;
        }

        $query->whereRaw('lower(venues.city) = lower(?)', [$this->criteria->city]);
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applyProximity(Builder $query): void
    {
        $near = $this->criteria->near;

        if ($near === null) {
            return;
        }

        $radiusMetres = $this->criteria->radiusKm * 1000;

        $query
            ->selectRaw(self::DISTANCE_SQL.' / 1000 AS distance_km', [$near->latitude, $near->longitude])
            // The bounding box is what the GiST index answers; the exact
            // distance check then trims the corners of the box.
            ->whereRaw(
                'earth_box(ll_to_earth(?, ?), ?) @> ll_to_earth(venues.latitude::float8, venues.longitude::float8)',
                [$near->latitude, $near->longitude, $radiusMetres],
            )
            ->whereRaw(self::DISTANCE_SQL.' <= ?', [$near->latitude, $near->longitude, $radiusMetres]);
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applyCourtFilters(Builder $query): void
    {
        if (! $this->criteria->hasCourtFilters()) {
            return;
        }

        $query->whereHas('courts', function (Builder $courts): void {
            /** @var Builder<Court> $courts */
            if ($this->criteria->courtType !== null) {
                $courts->where('court_type', $this->criteria->courtType);
            }

            if ($this->criteria->wallType !== null) {
                $courts->where('wall_type', $this->criteria->wallType);
            }

            if ($this->criteria->surface !== null) {
                $courts->where('surface', $this->criteria->surface);
            }
        });
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applyMinScore(Builder $query): void
    {
        if ($this->criteria->minScore === null) {
            return;
        }

        $query->where('venues.aggregate_score', '>=', $this->criteria->minScore);
    }

    /**
     * @param  Builder<Venue>  $query
     */
    private function applySort(Builder $query): void
    {
        $sort = $this->criteria->sort;
        $near = $this->criteria->near;

        // Distance ordering only means something relative to a point.
        if ($sort === VenueSort::Distance && $near === null) {
            $sort = VenueSort::Score;
        }

        match ($sort) {
            VenueSort::Score => $query->orderByDesc('venues.aggregate_score')->orderByDesc('venues.review_count'),
            VenueSort::Reviews => $query->orderByDesc('venues.review_count')->orderByDesc('venues.aggregate_score'),
            VenueSort::Distance => $query->orderByRaw(self::DISTANCE_SQL.' ASC', [$near->latitude, $near->longitude]),
            VenueSort::Name => $query->orderBy('venues.name'),
        };

        if ($this->criteria->term !== null) {
            $query->orderByRaw('ts_rank(venues.search_vector, '.self::TS_QUERY_SQL.') DESC', [$this->criteria->term]);
        }

        // A stable final key keeps pagination deterministic.
        $query->orderBy('venues.id');
    }
}
