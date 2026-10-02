<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatDate } from '@/lib/dates';
import { useNotifications } from '@/composables/useNotifications';

const { notifications, markRead, markAllRead } = useNotifications();
</script>

<template>
    <DropdownMenu v-if="notifications">
        <DropdownMenuTrigger as-child>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                class="relative"
                :aria-label="
                    notifications.unreadCount > 0
                        ? `Notifications, ${notifications.unreadCount} unread`
                        : 'Notifications'
                "
            >
                <Bell aria-hidden="true" />
                <span
                    v-if="notifications.unreadCount > 0"
                    class="bg-primary text-primary-foreground absolute -top-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full text-[10px] font-semibold tabular-nums"
                    aria-hidden="true"
                >
                    {{ Math.min(9, notifications.unreadCount) }}
                </span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <DropdownMenuLabel class="flex items-center justify-between">
                Notifications
                <button
                    v-if="notifications.unreadCount > 0"
                    type="button"
                    class="text-muted-foreground text-xs font-normal hover:underline"
                    @click="markAllRead"
                >
                    Mark all read
                </button>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <p
                v-if="notifications.items.length === 0"
                class="text-muted-foreground px-2 py-4 text-center text-sm"
            >
                Nothing yet.
            </p>
            <DropdownMenuItem
                v-for="item in notifications.items"
                :key="item.id"
                as-child
                class="flex items-start gap-2"
                :class="{ 'opacity-70': item.readAt !== null }"
                @select="item.readAt === null && markRead(item.id)"
            >
                <component
                    :is="item.url ? Link : 'div'"
                    :href="item.url ?? undefined"
                    class="flex w-full items-start gap-2"
                >
                    <span
                        class="mt-1.5 size-2 shrink-0 rounded-full"
                        :class="
                            item.readAt === null
                                ? 'bg-primary'
                                : 'bg-transparent'
                        "
                        aria-hidden="true"
                    />
                    <span class="flex-1 text-sm">
                        {{ item.message }}
                        <span class="text-muted-foreground block text-xs">
                            {{
                                formatDate(item.createdAt, { withYear: false })
                            }}
                            <template v-if="item.readAt === null">
                                · unread</template
                            >
                        </span>
                    </span>
                </component>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
