<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import Heading from '@/components/Heading.vue';
import ModerationReviewCard from '@/components/ModerationReviewCard.vue';
import type { ModerationReview } from '@/components/ModerationReviewCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ReviewOutcomeButtons from '@/components/ReviewOutcomeButtons.vue';
import type { Paginated } from '@/types';

defineProps<{ reviews: Paginated<ModerationReview> }>();
</script>

<template>
    <Head title="Flagged reviews" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Reviews with open flags"
            :description="`${reviews.total} awaiting a decision`"
        />

        <p v-if="reviews.data.length === 0" class="text-muted-foreground">
            Nothing to review.
        </p>

        <ul class="space-y-4">
            <li
                v-for="item in reviews.data"
                :key="item.review.id"
                class="space-y-3 rounded-xl border p-4"
            >
                <ModerationReviewCard :item="item" show-detail-link />
                <ReviewOutcomeButtons
                    :form="ResolveReviewFlagsController.form(item.review.id)"
                    publish-label="Dismiss flags, keep live"
                    remove-label="Remove review"
                />
            </li>
        </ul>

        <PaginationLinks :links="reviews.links" />
    </div>
</template>
