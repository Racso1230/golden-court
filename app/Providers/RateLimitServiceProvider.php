<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Named limiters for the write actions and the search endpoint. Per-user
 * limits fall back to the IP for the rare unauthenticated hit.
 */
class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('reviews', fn (Request $request): Limit => Limit::perDay(5)->by(self::userOrIp($request)));
        RateLimiter::for('flags', fn (Request $request): Limit => Limit::perHour(20)->by(self::userOrIp($request)));
        RateLimiter::for('claims', fn (Request $request): Limit => Limit::perDay(3)->by(self::userOrIp($request)));
        RateLimiter::for('votes', fn (Request $request): Limit => Limit::perMinute(60)->by(self::userOrIp($request)));
        RateLimiter::for('search', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip() ?? 'unknown'));
    }

    private static function userOrIp(Request $request): string
    {
        $user = $request->user();

        return $user === null ? 'ip:'.($request->ip() ?? 'unknown') : 'user:'.$user->id;
    }
}
