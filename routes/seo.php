<?php

declare(strict_types=1);

use App\Http\Controllers\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
