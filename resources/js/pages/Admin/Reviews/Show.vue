<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ChangeReviewStatusController from '@/actions/App/Http/Controllers/Admin/ChangeReviewStatusController';
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import Heading from '@/components/Heading.vue';
import ModerationReviewCard from '@/components/ModerationReviewCard.vue';
import type { ModerationReview } from '@/components/ModerationReviewCard.vue';
import ReviewOutcomeButtons from '@/components/ReviewOutcomeButtons.vue';
import { dashboard as adminDashboard } from '@/routes/admin';

defineProps<{ review: ModerationReview }>();
</script>

<template>
    <Head :title="`Review #${review.review.id}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Link :href="adminDashboard()" class="text-sm hover:underline">
            ← Moderation
        </Link>

        <Heading
            :title="`Review #${review.review.id}`"
            :description="`Currently ${review.statusLabel.toLowerCase()}`"
        />

        <div class="rounded-xl border p-4">
            <ModerationReviewCard :item="review" />
        </div>

        <section class="space-y-2">
            <h2 class="font-semibold">Decide</h2>
            <ReviewOutcomeButtons
                v-if="review.unresolvedFlagCount > 0"
                :form="ResolveReviewFlagsController.form(review.review.id)"
                publish-label="Dismiss flags, keep live"
                remove-label="Remove review"
            />
            <ReviewOutcomeButtons
                v-else
                :form="ChangeReviewStatusController.form(review.review.id)"
            />
        </section>

        <slot name="log" />
    </div>
</template>
