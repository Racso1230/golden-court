<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MapPin } from '@lucide/vue';
import GoldenCourtBadge from '@/components/GoldenCourtBadge.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { show as venueShow } from '@/routes/venues';
import type { VenueSummary } from '@/types';

defineProps<{ venue: VenueSummary }>();
</script>

<template>
    <article
        class="group bg-card hover:border-gold-300 relative flex h-full flex-col gap-3 rounded-xl border p-5 shadow-xs transition hover:shadow-sm"
    >
        <div class="min-w-0">
            <h3 class="text-base font-semibold">
                <!-- The link covers the whole card; the badges stay readable text. -->
                <Link
                    :href="venueShow(venue.slug)"
                    class="group-hover:underline after:absolute after:inset-0 after:rounded-xl"
                >
                    {{ venue.name }}
                </Link>
            </h3>
            <p
                class="text-muted-foreground mt-1 flex items-center gap-1 text-sm"
            >
                <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                {{ venue.city }} · {{ venue.courtCount }}
                {{ venue.courtCount === 1 ? 'court' : 'courts' }}
                <template v-if="venue.distanceKm !== null">
                    · {{ venue.distanceKm.toFixed(1) }} km away
                </template>
            </p>
        </div>
        <div class="mt-auto flex flex-wrap items-center gap-2">
            <ScoreBadge
                :score="venue.aggregateScore"
                :review-count="venue.reviewCount"
                size="sm"
            />
            <GoldenCourtBadge v-if="venue.hasGoldenCourt" :city="venue.city" />
        </div>
    </article>
</template>
