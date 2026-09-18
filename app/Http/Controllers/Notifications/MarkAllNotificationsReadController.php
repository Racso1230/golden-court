<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkAllNotificationsReadController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        $user->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
