<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show as adminReviewShow } from '@/routes/admin/reviews';
import { show as courtShow } from '@/routes/courts';

// Minimal shape for this phase; generated Data types arrive in Phase 7.
export type ModerationFlag = {
    id: number;
    reasonLabel: string;
    details: string | null;
    reporterDisplayName: string;
    createdAt: string;
    resolvedAt: string | null;
};

export type ModerationReview = {
    review: {
        id: number;
        overall: number;
        body: string;
        authorDisplayName: string;
        createdAt: string;
    };
    statusLabel: string;
    authorEmail: string;
    courtName: string;
    courtSlug: string;
    venueName: string;
    venueSlug: string;
    flags: ModerationFlag[];
    unresolvedFlagCount: number;
};

defineProps<{ item: ModerationReview; showDetailLink?: boolean }>();
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <Link
                :href="
                    courtShow({ venue: item.venueSlug, court: item.courtSlug })
                "
                class="font-semibold hover:underline"
            >
                {{ item.venueName }} · {{ item.courtName }}
            </Link>
            <p class="text-muted-foreground text-xs">
                {{ item.statusLabel }} · {{ item.review.overall.toFixed(1) }} ·
                by {{ item.review.authorDisplayName }} ({{ item.authorEmail }})
                ·
                {{ new Date(item.review.createdAt).toLocaleDateString() }}
            </p>
        </div>
        <p class="text-sm whitespace-pre-line">{{ item.review.body }}</p>
        <ul v-if="item.flags.length > 0" class="space-y-1 text-sm">
            <li
                v-for="flag in item.flags"
                :key="flag.id"
                class="text-muted-foreground"
            >
                <span class="text-foreground font-medium">{{
                    flag.reasonLabel
                }}</span>
                by {{ flag.reporterDisplayName }}
                <template v-if="flag.details"> — {{ flag.details }}</template>
                <template v-if="flag.resolvedAt"> (resolved)</template>
            </li>
        </ul>
        <Link
            v-if="showDetailLink"
            :href="adminReviewShow(item.review.id)"
            class="text-xs hover:underline"
        >
            Moderation detail
        </Link>
    </div>
</template>
