<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import RatingStars from '@/components/RatingStars.vue';
import { Badge } from '@/components/ui/badge';
import { show as adminReviewShow } from '@/routes/admin/reviews';
import { show as courtShow } from '@/routes/courts';
import type { ModerationReview } from '@/types';

defineProps<{ item: ModerationReview; showDetailLink?: boolean }>();

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
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
                <Badge variant="secondary">{{ item.statusLabel }}</Badge>
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
