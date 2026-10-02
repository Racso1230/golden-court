<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    MessageSquareReply,
    MessageSquareText,
    Pencil,
    ThumbsUp,
} from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import RatingStars from '@/components/RatingStars.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { reviewStatusTone } from '@/lib/status';
import { show as courtShow } from '@/routes/courts';
import { edit as reviewEdit } from '@/routes/reviews';
import { index as venuesIndex } from '@/routes/venues';
import type { Paginated } from '@/types';

type OwnReview = App.Domain.Reviews.Data.OwnReviewData;

defineProps<{ reviews: Paginated<OwnReview> }>();
</script>

<template>
    <PageHeader
        eyebrow="Your account"
        title="My reviews"
        :description="`${reviews.total} ${reviews.total === 1 ? 'review' : 'reviews'} written`"
    />

    <EmptyState
        v-if="reviews.data.length === 0"
        title="You have not reviewed a court yet"
        description="Find a venue you have played at and rate its courts."
    >
        <template #icon><MessageSquareText /></template>
        <Button as-child>
            <Link :href="venuesIndex()">Find a venue</Link>
        </Button>
    </EmptyState>

    <ul v-else class="space-y-4">
        <li
            v-for="item in reviews.data"
            :key="item.review.id"
            class="bg-card rounded-xl border p-5 shadow-xs"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <Link
                        :href="
                            courtShow({
                                venue: item.venueSlug,
                                court: item.courtSlug,
                            })
                        "
                        class="font-semibold hover:underline"
                    >
                        {{ item.courtName }} at {{ item.venueName }}
                    </Link>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <RatingStars :value="item.review.overall" size="sm" />
                        <span class="text-muted-foreground text-xs">
                            ·
                            <time :datetime="item.review.createdAt">{{
                                formatDate(item.review.createdAt)
                            }}</time>
                        </span>
                    </div>
                </div>
                <StatusBadge :tone="reviewStatusTone(item.review.status)">
                    {{ item.statusLabel }}
                </StatusBadge>
            </div>

            <p class="mt-3 line-clamp-3 text-sm leading-relaxed">
                {{ item.review.body }}
            </p>

            <div
                class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4"
            >
                <p
                    class="text-muted-foreground flex flex-wrap items-center gap-x-4 gap-y-1 text-xs"
                >
                    <span class="inline-flex items-center gap-1">
                        <ThumbsUp class="size-3.5" aria-hidden="true" />
                        {{ item.review.helpfulCount }} found this helpful
                    </span>
                    <span
                        v-if="item.review.reply"
                        class="text-gold-700 inline-flex items-center gap-1"
                    >
                        <MessageSquareReply
                            class="size-3.5"
                            aria-hidden="true"
                        />
                        The venue replied
                    </span>
                </p>
                <Button
                    v-if="item.canEdit"
                    as-child
                    size="sm"
                    variant="outline"
                >
                    <Link :href="reviewEdit(item.review.id)">
                        <Pencil aria-hidden="true" />
                        Edit or delete
                    </Link>
                </Button>
                <p v-else class="text-muted-foreground text-xs">
                    Under moderation, so it cannot be edited.
                </p>
            </div>
        </li>
    </ul>

    <div class="mt-8">
        <PaginationLinks :links="reviews.links" />
    </div>
</template>
