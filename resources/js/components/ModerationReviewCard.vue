<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import RatingStars from '@/components/RatingStars.vue';
import { Flag } from '@lucide/vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { formatDate } from '@/lib/dates';
import { reviewStatusTone } from '@/lib/status';
import { show as adminReviewShow } from '@/routes/admin/reviews';
import { show as courtShow } from '@/routes/courts';
import type { ModerationReview } from '@/types';

defineProps<{ item: ModerationReview; showDetailLink?: boolean }>();
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <Link
                :href="
                    courtShow({ venue: item.venueSlug, court: item.courtSlug })
                "
                class="font-semibold hover:underline"
            >
                {{ item.venueName }} · {{ item.courtName }}
            </Link>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <StatusBadge :tone="reviewStatusTone(item.review.status)">{{
                    item.statusLabel
                }}</StatusBadge>
                <RatingStars :value="item.review.overall" size="sm" />
                <span class="text-muted-foreground">
                    by {{ item.review.authorDisplayName }} ({{
                        item.authorEmail
                    }}) ·
                    {{ formatDate(item.review.createdAt) }}
                </span>
            </div>
        </div>
        <p class="text-sm whitespace-pre-line">{{ item.review.body }}</p>
        <ul
            v-if="item.flags.length > 0"
            class="bg-muted/60 space-y-1.5 rounded-lg p-3 text-sm"
            aria-label="Flags"
        >
            <li
                v-for="flag in item.flags"
                :key="flag.id"
                class="text-muted-foreground flex gap-2"
            >
                <Flag
                    class="text-destructive mt-0.5 size-3.5 shrink-0"
                    aria-hidden="true"
                />
                <span
                    ><span class="text-foreground font-medium">{{
                        flag.reasonLabel
                    }}</span>
                    by {{ flag.reporterDisplayName }}
                    <template v-if="flag.details">
                        — {{ flag.details }}</template
                    >
                    <template v-if="flag.resolvedAt">
                        (resolved)</template
                    ></span
                >
            </li>
        </ul>
        <Link
            v-if="showDetailLink"
            :href="adminReviewShow(item.review.id)"
            class="text-gold-700 text-xs font-medium hover:underline"
        >
            Moderation detail
        </Link>
    </div>
</template>
