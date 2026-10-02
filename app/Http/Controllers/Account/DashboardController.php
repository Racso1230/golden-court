<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Domain\Users\Queries\DashboardSummaryQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user() ?? abort(401);

        return Inertia::render('Dashboard', [
            'summary' => (new DashboardSummaryQuery($user))->get(),
        ]);
    }
}
