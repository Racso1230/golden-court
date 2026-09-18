<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Card, CardContent } from '@/components/ui/card';
import { show as courtShow } from '@/routes/courts';
import type { CourtSummary } from '@/types';

defineProps<{ court: CourtSummary; venueSlug: string }>();
</script>

<template>
    <Card class="py-4">
        <CardContent class="space-y-2">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <Link
                    :href="courtShow({ venue: venueSlug, court: court.slug })"
                    class="font-semibold hover:underline"
                >
                    {{ court.name }}
                </Link>
                <GoldenCourtBadge v-if="court.isGoldenCourt" />
            </div>
            <p class="text-muted-foreground text-sm">
                {{ court.courtTypeLabel }} · {{ court.wallTypeLabel }} ·
                {{ court.surfaceLabel }}
            </p>
            <ScoreBadge
                :score="court.aggregateScore"
                :review-count="court.reviewCount"
                size="sm"
            />
        </CardContent>
    </Card>
</template>
