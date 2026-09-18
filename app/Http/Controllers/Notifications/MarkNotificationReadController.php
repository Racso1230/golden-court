<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkNotificationReadController extends Controller
{
    public function __invoke(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        // Scoped to the user's own notifications, so someone else's id is simply a 404.
        $user->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back();
    }
}
