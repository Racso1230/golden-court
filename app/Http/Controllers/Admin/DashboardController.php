<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Moderation\Queries\ModerationCountsQuery;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ModerationCountsQuery $counts): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'counts' => $counts->get(),
        ]);
    }
}
