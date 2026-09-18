<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
            <label for="home-search" class="sr-only">
                Search venues or cities
            </label>
            <Input
                id="home-search"
                v-model="term"
                type="search"
                name="term"
                placeholder="Search venues or cities"
            />
            <Button type="submit">Search</Button>
        </form>
    </section>

    <section class="mt-10 space-y-4" aria-labelledby="top-venues-heading">
        <div class="flex items-baseline justify-between">
            <h2 id="top-venues-heading" class="text-2xl font-semibold">
                Top rated venues
            </h2>
            <Link :href="venuesIndex()" class="text-sm hover:underline">
                Browse all venues
            </Link>
        </div>
        <p v-if="topVenues.length === 0" class="text-muted-foreground">
            No venue has enough reviews to rank yet.
        </p>
        <ul v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="venue in topVenues" :key="venue.id">
                <VenueCard :venue="venue" />
            </li>
        </ul>
    </section>

    <section class="mt-10 space-y-4" aria-labelledby="recent-heading">
        <h2 id="recent-heading" class="text-2xl font-semibold">
            Latest reviews
        </h2>
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
                    <RatingStars :value="review.overall" size="sm" />
                </div>
                <p class="mt-2 text-sm">{{ review.excerpt }}</p>
                <p class="text-muted-foreground mt-2 text-xs">
                    {{ review.authorDisplayName }}
                </p>
            </li>
        </ul>
    </section>
</template>
