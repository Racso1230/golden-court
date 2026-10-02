<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    MessageSquareText,
    Search,
    Star,
    Trophy,
} from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import RatingStars from '@/components/RatingStars.vue';
import VenueCard from '@/components/VenueCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show as courtShow } from '@/routes/courts';
import { index as venuesIndex } from '@/routes/venues';
import type { RecentReview, VenueSummary } from '@/types';

defineProps<{
    topVenues: VenueSummary[];
    recentReviews: RecentReview[];
}>();

const term = ref('');

function search(): void {
    const query = term.value.trim();

    router.get(venuesIndex.url(), query ? { term: query } : {});
}

const steps = [
    {
        icon: Search,
        title: 'Find a venue',
        body: 'Search by name or city, or look for courts near you.',
    },
    {
        icon: Star,
        title: 'Compare the courts',
        body: 'Every court is scored on glass, lighting, turf and facilities.',
    },
    {
        icon: MessageSquareText,
        title: 'Rate where you play',
        body: 'Leave a review of each court you have played on.',
    },
];
</script>

<template>
    <section class="relative -mt-8 py-14 text-center sm:py-20 lg:-mt-10">
        <!-- A soft gold glow behind the hero; decorative only. -->
        <div
            class="from-gold-50 pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b to-transparent"
            aria-hidden="true"
        />
        <p
            class="text-gold-700 text-xs font-semibold tracking-[0.14em] uppercase"
        >
            Independent padel court reviews
        </p>
        <h1
            class="font-display mx-auto mt-4 max-w-3xl text-5xl leading-[1.05] tracking-tight sm:text-6xl"
        >
            Find the best
            <em class="text-gold-600 italic">padel courts</em> near you
        </h1>
        <p class="text-muted-foreground mx-auto mt-5 max-w-xl text-lg">
            Real players rate every court on glass, lighting, turf and
            facilities. Search by venue or city, or browse the leaders.
        </p>

        <form
            class="relative mx-auto mt-8 max-w-xl"
            role="search"
            aria-label="Find a venue"
            @submit.prevent="search"
        >
            <label for="home-search" class="sr-only">
                Search venues or cities
            </label>
            <Search
                class="text-muted-foreground pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2"
                aria-hidden="true"
            />
            <Input
                id="home-search"
                v-model="term"
                type="search"
                name="term"
                placeholder="Search venues or cities"
                class="shadow-gold-500/10 h-14 rounded-full pr-32 pl-12 text-base shadow-lg md:text-base"
            />
            <Button
                type="submit"
                class="absolute top-2 right-2 h-10 rounded-full px-6"
            >
                Search
            </Button>
        </form>

        <p
            class="text-muted-foreground mt-5 flex items-center justify-center gap-2 text-sm"
        >
            <Trophy class="text-gold-600 size-4" aria-hidden="true" />
            Scored on glass · lighting · turf · facilities
        </p>
    </section>

    <section class="mt-6 space-y-5" aria-labelledby="top-venues-heading">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2
                    id="top-venues-heading"
                    class="text-2xl font-semibold tracking-tight"
                >
                    Top rated venues
                </h2>
                <p class="text-muted-foreground mt-1 text-sm">
                    The best-scored venues with at least ten reviews.
                </p>
            </div>
            <Link
                :href="venuesIndex()"
                class="text-gold-700 inline-flex items-center gap-1 text-sm font-medium hover:underline"
            >
                Browse all venues
                <ArrowRight class="size-4" aria-hidden="true" />
            </Link>
        </div>
        <EmptyState
            v-if="topVenues.length === 0"
            title="No venue has enough reviews to rank yet"
            description="Venues appear here once players have reviewed them ten times."
        >
            <template #icon><Trophy /></template>
            <Button variant="outline" as-child>
                <Link :href="venuesIndex()">Browse venues</Link>
            </Button>
        </EmptyState>
        <ul v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="venue in topVenues" :key="venue.id">
                <VenueCard :venue="venue" />
            </li>
        </ul>
    </section>

    <section class="mt-16" aria-labelledby="how-heading">
        <h2 id="how-heading" class="sr-only">How Golden Court works</h2>
        <ol class="grid gap-4 sm:grid-cols-3">
            <li
                v-for="(step, index) in steps"
                :key="step.title"
                class="bg-muted/60 rounded-xl p-6"
            >
                <span
                    class="bg-gold-100 text-gold-800 flex size-10 items-center justify-center rounded-full"
                    aria-hidden="true"
                >
                    <component :is="step.icon" class="size-5" />
                </span>
                <p class="mt-4 font-semibold">
                    <span class="text-gold-700 tabular-nums"
                        >{{ index + 1 }}.</span
                    >
                    {{ step.title }}
                </p>
                <p class="text-muted-foreground mt-1 text-sm">
                    {{ step.body }}
                </p>
            </li>
        </ol>
    </section>

    <section class="mt-16 space-y-5" aria-labelledby="recent-heading">
        <h2 id="recent-heading" class="text-2xl font-semibold tracking-tight">
            Latest reviews
        </h2>
        <EmptyState
            v-if="recentReviews.length === 0"
            title="No reviews yet"
            description="Be the first to review a court you have played on."
        >
            <template #icon><MessageSquareText /></template>
        </EmptyState>
        <ul v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li
                v-for="review in recentReviews"
                :key="review.id"
                class="bg-card flex flex-col gap-3 rounded-xl border p-5 shadow-xs"
            >
                <RatingStars :value="review.overall" size="sm" />
                <p class="line-clamp-3 text-sm leading-relaxed">
                    “{{ review.excerpt }}”
                </p>
                <div class="mt-auto text-xs">
                    <Link
                        :href="
                            courtShow({
                                venue: review.venueSlug,
                                court: review.courtSlug,
                            })
                        "
                        class="font-semibold hover:underline"
                    >
                        {{ review.courtName }} at {{ review.venueName }}
                    </Link>
                    <p class="text-muted-foreground mt-0.5">
                        by {{ review.authorDisplayName }}
                    </p>
                </div>
            </li>
        </ul>
    </section>
</template>
