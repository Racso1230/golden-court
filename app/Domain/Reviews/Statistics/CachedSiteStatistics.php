<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Statistics;

use App\Domain\Reviews\Contracts\SiteStatistics;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\Cache;

/**
 * Computes the site-wide mean in SQL and caches it briefly: the prior moves
 * slowly, and every court recalculation would otherwise re-scan reviews.
 */
final class CachedSiteStatistics implements SiteStatistics
{
    public const string CACHE_KEY = 'site-statistics:mean-overall';

    /**
     * The neutral midpoint used until the first review exists.
     */
    public const float DEFAULT_MEAN = 3.0;

    private const int TTL_SECONDS = 600;

    public function meanOverallScore(): float
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function (): float {
            $mean = Review::query()
                ->published()
                ->toBase()
                ->selectRaw('AVG((glass_rating + lighting_rating + turf_rating + facilities_rating) / 4.0) AS mean')
                ->value('mean');

            return is_numeric($mean) ? round((float) $mean, 2) : self::DEFAULT_MEAN;
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
