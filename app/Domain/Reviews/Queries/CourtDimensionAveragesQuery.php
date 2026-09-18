<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Queries;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\DimensionAveragesData;
use App\Domain\Reviews\Models\Review;

/**
 * Per-dimension averages computed in SQL rather than by loading every review.
 */
final class CourtDimensionAveragesQuery
{
    public function __construct(private readonly Court $court) {}

    public function get(): DimensionAveragesData
    {
        $row = Review::query()
            ->published()
            ->where('court_id', $this->court->id)
            ->toBase()
            ->selectRaw('AVG(glass_rating) AS glass, AVG(lighting_rating) AS lighting, AVG(turf_rating) AS turf, AVG(facilities_rating) AS facilities')
            ->first();

        if ($row === null || $row->glass === null) {
            return DimensionAveragesData::none();
        }

        return new DimensionAveragesData(
            glass: self::round($row->glass),
            lighting: self::round($row->lighting),
            turf: self::round($row->turf),
            facilities: self::round($row->facilities),
        );
    }

    /**
     * @param  mixed  $value  AVG() comes back from PDO as a numeric string.
     */
    private static function round(mixed $value): ?float
    {
        return is_numeric($value) ? round((float) $value, 1) : null;
    }
}
