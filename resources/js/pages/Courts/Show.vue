<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import { show as courtShow } from '@/routes/courts';
import { create as reviewCreate } from '@/routes/reviews';
import { show as venueShow } from '@/routes/venues';
import type { Paginated } from '@/types';

// Minimal shapes for this phase; generated Data types arrive in Phase 7.
type Scores = {
    glass: number;
    lighting: number;
    turf: number;
    facilities: number;
};

type Review = {
    id: number;
    scores: Scores;
    overall: number;
    body: string;
    authorDisplayName: string;
    playedOn: string | null;
    createdAt: string;
    helpfulCount: number;
    hasVoted: boolean;
    reply: { id: number; body: string; authorDisplayName: string } | null;
};

type CourtDetail = {
    court: {
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
    venueName: string;
    venueSlug: string;
    venueCity: string;
    averages: {
        glass: number | null;
        lighting: number | null;
        turf: number | null;
        facilities: number | null;
    };
    reviews: Paginated<Review>;
};

type Option = { value: string; label: string };

const props = defineProps<{
    court: CourtDetail;
    sort: string;
    sortOptions: Option[];
    canReview: boolean;
}>();

const dimensions: { key: keyof Scores; label: string }[] = [
    { key: 'glass', label: 'Glass' },
    { key: 'lighting', label: 'Lighting' },
    { key: 'turf', label: 'Turf' },
    { key: 'facilities', label: 'Facilities' },
];

function changeSort(event: Event): void {
    const sort = (event.target as HTMLSelectElement).value;

    router.get(
        courtShow.url({
            venue: props.court.venueSlug,
            court: props.court.court.slug,
        }),
        { sort },
        { preserveScroll: true },
    );
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="`${court.court.name} at ${court.venueName}`" />

    <Link :href="venueShow(court.venueSlug)" class="text-sm hover:underline">
        ← {{ court.venueName }}
    </Link>

    <header class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight">
                {{ court.court.name }}
                <span
                    v-if="court.court.isGoldenCourt"
                    class="ml-2 align-middle text-sm font-medium text-amber-700 dark:text-amber-300"
                    >Golden Court of {{ court.venueCity }}</span
                >
            </h1>
            <p class="text-muted-foreground text-sm">
                {{ court.court.courtTypeLabel }} ·
                {{ court.court.wallTypeLabel }} ·
                {{ court.court.surfaceLabel }}
            </p>
            <ScoreBadge
                :score="court.court.aggregateScore"
                :review-count="court.court.reviewCount"
            />
        </div>
        <Button v-if="canReview" as-child>
            <Link :href="reviewCreate(court.court.id)">Write a review</Link>
        </Button>
    </header>

    <section class="mt-6 grid gap-3 sm:grid-cols-4">
        <div
            v-for="dimension in dimensions"
            :key="dimension.key"
            class="rounded-xl border p-3"
        >
            <p class="text-muted-foreground text-xs uppercase">
                {{ dimension.label }}
            </p>
            <p class="text-xl font-semibold tabular-nums">
                {{
                    court.averages[dimension.key] === null
                        ? '–'
                        : court.averages[dimension.key]?.toFixed(1)
                }}
            </p>
        </div>
    </section>

    <section class="mt-8 space-y-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold">
                Reviews ({{ court.reviews.total }})
            </h2>
            <label class="flex items-center gap-2 text-sm">
                Sort
                <select
                    class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs"
                    :value="sort"
                    @change="changeSort"
                >
                    <option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
        </div>

        <p v-if="court.reviews.data.length === 0" class="text-muted-foreground">
            No reviews yet.
        </p>

        <ul class="space-y-4">
            <li
                v-for="review in court.reviews.data"
                :key="review.id"
                class="rounded-xl border p-4"
            >
                <div
                    class="flex flex-wrap items-baseline justify-between gap-2"
                >
                    <p class="font-semibold">{{ review.authorDisplayName }}</p>
                    <p class="text-muted-foreground text-xs">
                        {{ formatDate(review.createdAt) }}
                        <template v-if="review.playedOn">
                            · played {{ formatDate(review.playedOn) }}
                        </template>
                    </p>
                </div>
                <p class="mt-1 text-sm">
                    <span class="font-semibold tabular-nums">{{
                        review.overall.toFixed(1)
                    }}</span>
                    <span class="text-muted-foreground">
                        · glass {{ review.scores.glass }} · lighting
                        {{ review.scores.lighting }} · turf
                        {{ review.scores.turf }} · facilities
                        {{ review.scores.facilities }}
                    </span>
                </p>
                <p class="mt-3 whitespace-pre-line">{{ review.body }}</p>
                <p class="text-muted-foreground mt-3 text-xs">
                    {{ review.helpfulCount }} found this helpful
                    <template v-if="review.hasVoted">· including you</template>
                </p>
                <blockquote
                    v-if="review.reply"
                    class="bg-muted mt-3 rounded-md p-3 text-sm"
                >
                    <p class="font-semibold">
                        Reply from {{ review.reply.authorDisplayName }}
                    </p>
                    <p class="mt-1 whitespace-pre-line">
                        {{ review.reply.body }}
                    </p>
                </blockquote>
            </li>
        </ul>

        <PaginationLinks :links="court.reviews.links" />
    </section>
</template>
