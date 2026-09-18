<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show as courtShow } from '@/routes/courts';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';

// Minimal shapes for this phase; generated Data types arrive in Phase 7.
type VenueSummary = {
    id: number;
    name: string;
    slug: string;
    city: string;
    aggregateScore: number;
    reviewCount: number;
    courtCount: number;
    hasGoldenCourt: boolean;
};

type RecentReview = {
    id: number;
    overall: number;
    excerpt: string;
    authorDisplayName: string;
    courtName: string;
    courtSlug: string;
    venueName: string;
    venueSlug: string;
    createdAt: string;
};

defineProps<{
    topVenues: VenueSummary[];
    recentReviews: RecentReview[];
}>();

const term = ref('');

function search(): void {
    router.get(venuesIndex.url(), term.value ? { term: term.value } : {});
}
</script>

<template>
    <Head title="Find your next court" />

    <section class="space-y-6 py-8 text-center">
        <h1 class="text-4xl font-bold tracking-tight">
            Find the best padel courts near you
        </h1>
        <p class="text-muted-foreground mx-auto max-w-xl">
            Real players rate every court on glass, lighting, turf and
            facilities. Search by venue or city, or browse the leaders.
        </p>
        <form
            class="mx-auto flex max-w-lg gap-2"
            role="search"
            @submit.prevent="search"
        >
            <Input
                v-model="term"
                type="search"
                name="term"
                placeholder="Search venues or cities"
                aria-label="Search venues or cities"
            />
            <Button type="submit">Search</Button>
        </form>
    </section>

    <section class="mt-10 space-y-4">
        <div class="flex items-baseline justify-between">
            <h2 class="text-2xl font-semibold">Top rated venues</h2>
            <Link :href="venuesIndex()" class="text-sm hover:underline">
                Browse all venues
            </Link>
        </div>
        <p v-if="topVenues.length === 0" class="text-muted-foreground">
            No venue has enough reviews to rank yet.
        </p>
        <ul v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li
                v-for="venue in topVenues"
                :key="venue.id"
                class="rounded-xl border p-4"
            >
                <Link
                    :href="venueShow(venue.slug)"
                    class="font-semibold hover:underline"
                >
                    {{ venue.name }}
                </Link>
                <p class="text-muted-foreground text-sm">
                    {{ venue.city }} · {{ venue.courtCount }}
                    {{ venue.courtCount === 1 ? 'court' : 'courts' }}
                </p>
                <div class="mt-2 flex items-center gap-2">
                    <ScoreBadge
                        :score="venue.aggregateScore"
                        :review-count="venue.reviewCount"
                    />
                    <span
                        v-if="venue.hasGoldenCourt"
                        class="text-xs font-medium text-amber-700 dark:text-amber-300"
                        >Golden Court</span
                    >
                </div>
            </li>
        </ul>
    </section>

    <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-semibold">Latest reviews</h2>
        <p v-if="recentReviews.length === 0" class="text-muted-foreground">
            No reviews yet. Be the first.
        </p>
        <ul v-else class="grid gap-4 sm:grid-cols-2">
            <li
                v-for="review in recentReviews"
                :key="review.id"
                class="rounded-xl border p-4"
            >
                <div class="flex items-baseline justify-between gap-2">
                    <Link
                        :href="
                            courtShow({
                                venue: review.venueSlug,
                                court: review.courtSlug,
                            })
                        "
                        class="font-semibold hover:underline"
                    >
                        {{ review.venueName }} · {{ review.courtName }}
                    </Link>
                    <span class="text-sm font-semibold tabular-nums">
                        {{ review.overall.toFixed(1) }}
                    </span>
                </div>
                <p class="mt-2 text-sm">{{ review.excerpt }}</p>
                <p class="text-muted-foreground mt-2 text-xs">
                    {{ review.authorDisplayName }}
                </p>
            </li>
        </ul>
    </section>
</template>
