<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ChangeReviewStatusController from '@/actions/App/Http/Controllers/Admin/ChangeReviewStatusController';
import ResolveReviewFlagsController from '@/actions/App/Http/Controllers/Admin/ResolveReviewFlagsController';
import Heading from '@/components/Heading.vue';
import ModerationReviewCard from '@/components/ModerationReviewCard.vue';
import type { ModerationReview } from '@/components/ModerationReviewCard.vue';
import ReviewOutcomeButtons from '@/components/ReviewOutcomeButtons.vue';
import { dashboard as adminDashboard } from '@/routes/admin';

// Minimal shape for this phase; generated Data types arrive in Phase 7.
type LogEntry = {
    id: number;
    actionLabel: string;
    actorLabel: string;
    actorDisplayName: string | null;
    details: Record<string, unknown>;
    createdAt: string;
};

defineProps<{ review: ModerationReview; log: LogEntry[] }>();

function describeDetails(details: Record<string, unknown>): string {
    return Object.entries(details)
        .map(([key, value]) => `${key}: ${String(value)}`)
        .join(', ');
}
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

        <section class="space-y-2">
            <h2 class="font-semibold">Moderation history</h2>
            <p v-if="log.length === 0" class="text-muted-foreground text-sm">
                No moderation actions recorded yet.
            </p>
            <ol v-else class="space-y-1 text-sm">
                <li
                    v-for="entry in log"
                    :key="entry.id"
                    class="flex flex-wrap gap-x-2"
                >
                    <span class="text-muted-foreground tabular-nums">
                        {{ new Date(entry.createdAt).toLocaleString() }}
                    </span>
                    <span class="font-medium">{{ entry.actionLabel }}</span>
                    <span class="text-muted-foreground">
                        by {{ entry.actorDisplayName ?? entry.actorLabel }}
                    </span>
                    <span
                        v-if="Object.keys(entry.details).length > 0"
                        class="text-muted-foreground"
                    >
                        ({{ describeDetails(entry.details) }})
                    </span>
                </li>
            </ol>
        </section>
    </div>
</template>
