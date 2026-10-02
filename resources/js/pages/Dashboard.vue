<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Building2,
    MessageSquareReply,
    MessageSquareText,
    Search,
    Settings,
    ShieldCheck,
    ThumbsUp,
} from '@lucide/vue';
import { computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { useNotifications } from '@/composables/useNotifications';
import { formatDate } from '@/lib/dates';
import {
    claims as accountClaims,
    reviews as accountReviews,
} from '@/routes/account';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as editProfile } from '@/routes/profile';
import { index as venuesIndex } from '@/routes/venues';
import type { DashboardSummary } from '@/types';

const props = defineProps<{ summary: DashboardSummary }>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value.role === 'admin');
const isOwner = computed(() => props.summary.ownedVenueCount > 0);
const { notifications, markRead, markAllRead } = useNotifications();

function plural(count: number, word: string): string {
    return `${count} ${word}${count === 1 ? '' : 's'}`;
}

const actions = computed(() => [
    {
        title: 'Find a venue',
        description: 'Search by name, city or distance',
        href: venuesIndex(),
        icon: Search,
    },
    {
        title: 'My reviews',
        description: 'Edit or delete what you have written',
        href: accountReviews(),
        icon: MessageSquareText,
    },
    {
        title: 'My claims',
        description: 'Venues you have asked to manage',
        href: accountClaims(),
        icon: Building2,
    },
    {
        title: 'Settings',
        description: 'Profile, password and two-factor',
        href: editProfile(),
        icon: Settings,
    },
    ...(isAdmin.value
        ? [
              {
                  title: 'Moderation',
                  description: 'Claims, flags and pending reviews',
                  href: adminDashboard(),
                  icon: ShieldCheck,
              },
          ]
        : []),
]);
</script>

<template>
    <PageHeader
        eyebrow="Your account"
        :title="`Welcome back, ${user.display_name}`"
        description="Your reviews, claims and notifications in one place."
    />

    <section aria-labelledby="summary-heading" class="mb-10">
        <h2 id="summary-heading" class="sr-only">Summary</h2>
        <ul
            class="grid gap-4 sm:grid-cols-2"
            :class="isOwner ? 'lg:grid-cols-4' : 'lg:grid-cols-3'"
        >
            <li>
                <StatCard
                    label="Reviews written"
                    :value="summary.reviewCount"
                    :hint="
                        summary.pendingReviewCount > 0
                            ? `${summary.pendingReviewCount} awaiting moderation`
                            : 'All published'
                    "
                    :href="accountReviews()"
                >
                    <template #icon><MessageSquareText /></template>
                </StatCard>
            </li>
            <li>
                <StatCard
                    label="Helpful votes"
                    :value="summary.helpfulVoteCount"
                    hint="From other players"
                    :href="accountReviews()"
                >
                    <template #icon><ThumbsUp /></template>
                </StatCard>
            </li>
            <li>
                <StatCard
                    label="Venue claims"
                    :value="summary.claimCount"
                    :hint="
                        summary.pendingClaimCount > 0
                            ? `${summary.pendingClaimCount} awaiting a decision`
                            : 'None pending'
                    "
                    :href="accountClaims()"
                >
                    <template #icon><Building2 /></template>
                </StatCard>
            </li>
            <li v-if="isOwner">
                <StatCard
                    label="Reviews to answer"
                    :value="summary.unansweredReviewCount"
                    :hint="`Across ${plural(summary.ownedVenueCount, 'venue')} you own`"
                    :href="venuesIndex()"
                >
                    <template #icon><MessageSquareReply /></template>
                </StatCard>
            </li>
        </ul>
    </section>

    <div class="grid gap-10 lg:grid-cols-[1fr_22rem]">
        <section aria-labelledby="notifications-heading">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2
                    id="notifications-heading"
                    class="text-xl font-semibold tracking-tight"
                >
                    Notifications
                </h2>
                <Button
                    v-if="notifications && notifications.unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    @click="markAllRead"
                >
                    Mark all read
                </Button>
            </div>

            <EmptyState
                v-if="!notifications || notifications.items.length === 0"
                title="You're all caught up"
                description="Replies to your reviews and claim decisions will appear here."
            >
                <template #icon><Bell /></template>
            </EmptyState>

            <ul
                v-else
                class="bg-card divide-y overflow-hidden rounded-xl border shadow-xs"
            >
                <li
                    v-for="item in notifications.items"
                    :key="item.id"
                    class="flex items-start gap-3 px-5 py-4"
                >
                    <span
                        class="mt-1.5 size-2 shrink-0 rounded-full"
                        :class="
                            item.readAt === null
                                ? 'bg-gold-500'
                                : 'bg-transparent'
                        "
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 text-sm">
                        <component
                            :is="item.url ? Link : 'p'"
                            :href="item.url ?? undefined"
                            :class="{
                                'font-medium hover:underline': item.url,
                                'text-muted-foreground': item.readAt !== null,
                            }"
                        >
                            {{ item.message }}
                        </component>
                        <p class="text-muted-foreground mt-0.5 text-xs">
                            <time :datetime="item.createdAt">{{
                                formatDate(item.createdAt)
                            }}</time>
                            <template v-if="item.readAt === null">
                                · unread</template
                            >
                        </p>
                    </div>
                    <Button
                        v-if="item.readAt === null"
                        variant="ghost"
                        size="sm"
                        class="shrink-0"
                        @click="markRead(item.id)"
                    >
                        Mark read
                    </Button>
                </li>
            </ul>
        </section>

        <section aria-labelledby="actions-heading">
            <h2
                id="actions-heading"
                class="mb-4 text-xl font-semibold tracking-tight"
            >
                Quick links
            </h2>
            <ul class="space-y-3">
                <li v-for="action in actions" :key="action.title">
                    <Link
                        :href="action.href"
                        class="bg-card hover:border-gold-300 flex items-center gap-4 rounded-xl border p-4 shadow-xs transition hover:shadow-sm"
                    >
                        <span
                            class="bg-gold-50 text-gold-700 flex size-10 shrink-0 items-center justify-center rounded-full"
                        >
                            <component
                                :is="action.icon"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <span>
                            <span class="block font-medium">{{
                                action.title
                            }}</span>
                            <span class="text-muted-foreground block text-xs">{{
                                action.description
                            }}</span>
                        </span>
                    </Link>
                </li>
            </ul>
        </section>
    </div>
</template>
