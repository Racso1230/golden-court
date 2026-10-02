<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seo;

use App\Domain\Venues\Actions\BuildSitemapAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(BuildSitemapAction $sitemap): Response
    {
        return response()
            ->view('seo.sitemap', ['entries' => $sitemap->handle()])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age='.BuildSitemapAction::CACHE_SECONDS);
    }
}
