<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { show as courtShow } from '@/routes/courts';
import { index as venuesIndex } from '@/routes/venues';

// Minimal shapes for this phase; generated Data types arrive in Phase 7.
type CourtSummary = {
    id: number;
    name: string;
    slug: string;
    courtTypeLabel: string;
    wallTypeLabel: string;
    surfaceLabel: string;
    aggregateScore: number;
    reviewCount: number;
    isGoldenCourt: boolean;
};

type VenueDetail = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    addressLine1: string;
    addressLine2: string | null;
    city: string;
    postcode: string;
    website: string | null;
    phone: string | null;
    aggregateScore: number;
    reviewCount: number;
    courtCount: number;
    ownerDisplayName: string | null;
    courts: CourtSummary[];
};

defineProps<{ venue: VenueDetail }>();
</script>

<template>
    <Head :title="venue.name" />

    <Link :href="venuesIndex()" class="text-sm hover:underline">
        ← All venues
    </Link>

    <header class="mt-4 space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">{{ venue.name }}</h1>
        <ScoreBadge
            :score="venue.aggregateScore"
            :review-count="venue.reviewCount"
        />
        <address class="text-muted-foreground text-sm not-italic">
            {{ venue.addressLine1
            }}<template v-if="venue.addressLine2"
                >, {{ venue.addressLine2 }}</template
            >, {{ venue.city }} {{ venue.postcode }}
        </address>
        <p class="text-muted-foreground flex gap-4 text-sm">
            <a
                v-if="venue.website"
                :href="venue.website"
                rel="noopener"
                target="_blank"
                class="hover:underline"
                >Website</a
            >
            <span v-if="venue.phone">{{ venue.phone }}</span>
            <span v-if="venue.ownerDisplayName"
                >Managed by {{ venue.ownerDisplayName }}</span
            >
        </p>
        <p v-if="venue.description" class="max-w-2xl">
            {{ venue.description }}
        </p>
    </header>

    <section class="mt-8 space-y-4">
        <h2 class="text-2xl font-semibold">Courts ({{ venue.courtCount }})</h2>
        <ul class="grid gap-4 sm:grid-cols-2">
            <li
                v-for="court in venue.courts"
                :key="court.id"
                class="rounded-xl border p-4"
            >
                <div class="flex items-baseline justify-between gap-2">
                    <Link
                        :href="
                            courtShow({ venue: venue.slug, court: court.slug })
                        "
                        class="font-semibold hover:underline"
                    >
                        {{ court.name }}
                    </Link>
                    <span
                        v-if="court.isGoldenCourt"
                        class="text-xs font-medium text-amber-700 dark:text-amber-300"
                        >Golden Court</span
                    >
                </div>
                <p class="text-muted-foreground text-sm">
                    {{ court.courtTypeLabel }} · {{ court.wallTypeLabel }} ·
                    {{ court.surfaceLabel }}
                </p>
                <div class="mt-2">
                    <ScoreBadge
                        :score="court.aggregateScore"
                        :review-count="court.reviewCount"
                    />
                </div>
            </li>
        </ul>
    </section>
</template>
