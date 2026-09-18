<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as venuesIndex, show as venueShow } from '@/routes/venues';
import type { Paginated } from '@/types';

// Minimal shapes for this phase; generated Data types arrive in Phase 7.
type VenueSummary = {
    id: number;
    name: string;
    slug: string;
    city: string;
    aggregateScore: number;
    reviewCount: number;
    courtCount: number;
    distanceKm: number | null;
    hasGoldenCourt: boolean;
};

type Option = { value: string; label: string };

type Filters = {
    term?: string | null;
    city?: string | null;
    lat?: string | null;
    lng?: string | null;
    radius?: string | null;
    court_type?: string | null;
    wall_type?: string | null;
    surface?: string | null;
    min_score?: string | null;
    sort?: string | null;
};

const props = defineProps<{
    venues: Paginated<VenueSummary>;
    filters: Filters;
    options: {
        courtTypes: Option[];
        wallTypes: Option[];
        surfaces: Option[];
        sorts: Option[];
    };
}>();

const form = reactive({
    term: props.filters.term ?? '',
    city: props.filters.city ?? '',
    lat: props.filters.lat ?? '',
    lng: props.filters.lng ?? '',
    radius: props.filters.radius ?? '',
    court_type: props.filters.court_type ?? '',
    wall_type: props.filters.wall_type ?? '',
    surface: props.filters.surface ?? '',
    min_score: props.filters.min_score ?? '',
    sort: props.filters.sort ?? 'score',
});

function search(): void {
    const query = Object.fromEntries(
        Object.entries(form).filter(([, value]) => value !== ''),
    );

    router.get(venuesIndex.url(), query, { preserveState: true });
}

const selectClass =
    'border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs';
</script>

<template>
    <Head title="Venues" />

    <h1 class="text-3xl font-bold tracking-tight">Venues</h1>

    <form
        class="mt-6 grid gap-4 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-4"
        @submit.prevent="search"
    >
        <div class="grid gap-1.5 lg:col-span-2">
            <Label for="term">Search</Label>
            <Input
                id="term"
                v-model="form.term"
                type="search"
                placeholder="Venue or city"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="city">City</Label>
            <Input
                id="city"
                v-model="form.city"
                placeholder="e.g. Manchester"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="sort">Sort by</Label>
            <select id="sort" v-model="form.sort" :class="selectClass">
                <option
                    v-for="option in options.sorts"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div class="grid gap-1.5">
            <Label for="court_type">Court type</Label>
            <select
                id="court_type"
                v-model="form.court_type"
                :class="selectClass"
            >
                <option value="">Any</option>
                <option
                    v-for="option in options.courtTypes"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
        <div class="grid gap-1.5">
            <Label for="wall_type">Walls</Label>
            <select
                id="wall_type"
                v-model="form.wall_type"
                :class="selectClass"
            >
                <option value="">Any</option>
                <option
                    v-for="option in options.wallTypes"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
        <div class="grid gap-1.5">
            <Label for="surface">Surface</Label>
            <select id="surface" v-model="form.surface" :class="selectClass">
                <option value="">Any</option>
                <option
                    v-for="option in options.surfaces"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
        <div class="grid gap-1.5">
            <Label for="min_score">Minimum score</Label>
            <Input
                id="min_score"
                v-model="form.min_score"
                type="number"
                min="0"
                max="5"
                step="0.5"
            />
        </div>

        <div class="grid gap-1.5">
            <Label for="lat">Latitude</Label>
            <Input id="lat" v-model="form.lat" type="number" step="any" />
        </div>
        <div class="grid gap-1.5">
            <Label for="lng">Longitude</Label>
            <Input id="lng" v-model="form.lng" type="number" step="any" />
        </div>
        <div class="grid gap-1.5">
            <Label for="radius">Within (km)</Label>
            <Input
                id="radius"
                v-model="form.radius"
                type="number"
                min="1"
                max="200"
                placeholder="25"
            />
        </div>
        <div class="flex items-end">
            <Button type="submit" class="w-full">Search</Button>
        </div>
    </form>

    <p class="text-muted-foreground mt-6 text-sm">
        {{ venues.total }} {{ venues.total === 1 ? 'venue' : 'venues' }} found
    </p>

    <ul class="mt-4 space-y-3">
        <li
            v-for="venue in venues.data"
            :key="venue.id"
            class="flex flex-wrap items-start justify-between gap-3 rounded-xl border p-4"
        >
            <div>
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
            <div class="flex items-center gap-2">
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

    <div class="mt-6">
        <PaginationLinks :links="venues.links" />
    </div>
</template>
