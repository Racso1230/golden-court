import { router, usePage } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import MarkAllNotificationsReadController from '@/actions/App/Http/Controllers/Notifications/MarkAllNotificationsReadController';
import MarkNotificationReadController from '@/actions/App/Http/Controllers/Notifications/MarkNotificationReadController';

type NotificationsSummary = App.Domain.Users.Data.NotificationsSummaryData;

export type UseNotificationsReturn = {
    /** Null for guests. */
    notifications: ComputedRef<NotificationsSummary | null>;
    markRead: (id: string) => void;
    markAllRead: () => void;
};

/**
 * The signed-in user's notifications from the shared `notifications` prop,
 * with the two actions that change them.
 */
export function useNotifications(): UseNotificationsReturn {
    const page = usePage();

    return {
        notifications: computed(() => page.props.notifications),
        markRead: (id: string): void => {
            router.post(
                MarkNotificationReadController.url(id),
                {},
                { preserveScroll: true },
            );
        },
        markAllRead: (): void => {
            router.post(
                MarkAllNotificationsReadController.url(),
                {},
                { preserveScroll: true },
            );
        },
    };
}
