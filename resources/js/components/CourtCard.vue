<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { show as courtShow } from '@/routes/courts';
import type { CourtSummary } from '@/types';

defineProps<{ court: CourtSummary; venueSlug: string }>();
</script>

<template>
    <article
        class="group bg-card hover:border-gold-300 relative flex h-full flex-col gap-2 rounded-xl border p-5 shadow-xs transition hover:shadow-sm"
    >
        <div class="flex flex-wrap items-start justify-between gap-2">
            <h3 class="text-base font-semibold">
                <Link
                    :href="courtShow({ venue: venueSlug, court: court.slug })"
                    class="group-hover:underline after:absolute after:inset-0 after:rounded-xl"
                >
                    {{ court.name }}
                </Link>
            </h3>
            <GoldenCourtBadge v-if="court.isGoldenCourt" />
        </div>
        <p class="text-muted-foreground text-sm">
            {{ court.courtTypeLabel }} · {{ court.wallTypeLabel }} walls ·
            {{ court.surfaceLabel }}
        </p>
        <div class="mt-auto pt-1">
            <ScoreBadge
                :score="court.aggregateScore"
                :review-count="court.reviewCount"
                size="sm"
            />
        </div>
    </article>
</template>
