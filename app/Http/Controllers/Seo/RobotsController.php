<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Support\Seo\Site;
use Illuminate\Http\Response;

/**
 * Served by the application rather than as a static file so the Sitemap line
 * always carries the configured site URL. Login and registration stay
 * crawlable so their noindex is honoured; venue listings stay crawlable so
 * city and page URLs can be indexed, and only the endless geographic and
 * score parameters are blocked.
 */
class RobotsController extends Controller
{
    /**
     * @var list<string>
     */
    public const array DISALLOW = [
        '/admin',
        '/account',
        '/dashboard',
        '/settings',
        '/reviews',
        '/courts/*/reviews',
        '/notifications',
        '/user',
        '/two-factor-challenge',
        '/email',
        '/passkeys',
        '/up',
        '/*?*lat=',
        '/*?*lng=',
        '/*?*radius=',
        '/*?*min_score=',
    ];

    public function __invoke(Site $site): Response
    {
        return response()
            ->view('seo.robots', ['disallow' => self::DISALLOW, 'sitemap' => $site->url('/sitemap.xml')])
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
