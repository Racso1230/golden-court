<?php

declare(strict_types=1);

namespace App\Domain\Users\Data;

use App\Domain\Users\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * What the header bell needs: the unread count and the latest few items.
 */
#[TypeScript]
final class NotificationsSummaryData extends Data
{
    /**
     * @param  array<int, NotificationData>  $items
     */
    public function __construct(
        public int $unreadCount,
        public array $items,
    ) {}

    public static function forUser(User $user, int $limit = 8): self
    {
        return new self(
            unreadCount: $user->unreadNotifications()->count(),
            items: $user->notifications()
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (DatabaseNotification $notification): NotificationData => NotificationData::fromModel($notification))
                ->values()
                ->all(),
        );
    }
}
