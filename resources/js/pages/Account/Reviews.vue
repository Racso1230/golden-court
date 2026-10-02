<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import RatingStars from '@/components/RatingStars.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { show as courtShow } from '@/routes/courts';
import { edit as reviewEdit } from '@/routes/reviews';
import { index as venuesIndex } from '@/routes/venues';
import type { Paginated } from '@/types';

type OwnReview = App.Domain.Reviews.Data.OwnReviewData;

defineProps<{ reviews: Paginated<OwnReview> }>();

function statusVariant(
    status: App.Domain.Reviews.Enums.ReviewStatus,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'published':
            return 'default';
        case 'pending':
            return 'secondary';
        case 'flagged':
            return 'outline';
        case 'removed':
            return 'destructive';
    }
}
</script>

<template>
    <Head title="My reviews" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="My reviews"
            :description="`${reviews.total} ${reviews.total === 1 ? 'review' : 'reviews'} written`"
        />

        <p v-if="reviews.data.length === 0" class="text-muted-foreground">
            You have not reviewed a court yet.
            <Link :href="venuesIndex()" class="underline">Find a venue</Link>
            to get started.
        </p>

        <ul class="space-y-4">
            <li
                v-for="item in reviews.data"
                :key="item.review.id"
                class="space-y-2 rounded-xl border p-4"
            >
                <div
                    class="flex flex-wrap items-baseline justify-between gap-2"
                >
                    <Link
                        :href="
                            courtShow({
                                venue: item.venueSlug,
                                court: item.courtSlug,
                            })
                        "
                        class="font-semibold hover:underline"
                    >
                        {{ item.venueName }} · {{ item.courtName }}
                    </Link>
                    <div class="flex items-center gap-2">
                        <Badge :variant="statusVariant(item.review.status)">
                            {{ item.statusLabel }}
                        </Badge>
                        <span class="text-muted-foreground text-xs">
                            {{ formatDate(item.review.createdAt) }}
                        </span>
                    </div>
                </div>
                <RatingStars :value="item.review.overall" size="sm" />
                <p class="line-clamp-3 text-sm">{{ item.review.body }}</p>
                <p class="text-muted-foreground text-xs">
                    {{ item.review.helpfulCount }} found this helpful
                    <template v-if="item.review.reply">
                        · the venue replied
                    </template>
                </p>
                <div v-if="item.canEdit" class="flex gap-2">
                    <Button as-child size="sm" variant="outline">
                        <Link :href="reviewEdit(item.review.id)">
                            Edit or delete
                        </Link>
                    </Button>
                </div>
                <p v-else class="text-muted-foreground text-xs">
                    This review is under moderation and cannot be edited.
                </p>
            </li>
        </ul>

        <PaginationLinks :links="reviews.links" />
    </div>
</template>
