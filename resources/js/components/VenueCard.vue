<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Card, CardContent } from '@/components/ui/card';
import { show as venueShow } from '@/routes/venues';
import type { VenueSummary } from '@/types';

defineProps<{ venue: VenueSummary }>();
</script>

<template>
    <Card class="py-4">
        <CardContent class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <Link
                    :href="venueShow(venue.slug)"
                    class="font-semibold hover:underline"
                >
                    {{ venue.name }}
                </Link>
                <p class="text-muted-foreground text-sm">
                    {{ venue.city }} · {{ venue.courtCount }}
                    {{ venue.courtCount === 1 ? 'court' : 'courts' }}
                    <template v-if="venue.distanceKm !== null">
                        · {{ venue.distanceKm.toFixed(1) }} km away
                    </template>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <ScoreBadge
                    :score="venue.aggregateScore"
                    :review-count="venue.reviewCount"
                    size="sm"
                />
                <GoldenCourtBadge v-if="venue.hasGoldenCourt" />
            </div>
        </CardContent>
    </Card>
</template>
