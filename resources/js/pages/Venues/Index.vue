<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import NativeSelect from '@/components/NativeSelect.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import VenueCard from '@/components/VenueCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useVenueSearch } from '@/composables/useVenueSearch';
import type {
    CourtType,
    Option,
    Paginated,
    Surface,
    VenueSearchCriteria,
    VenueSort,
    VenueSummary,
    WallType,
} from '@/types';

const props = defineProps<{
    venues: Paginated<VenueSummary>;
    criteria: VenueSearchCriteria;
    options: {
        courtTypes: Option[];
        wallTypes: Option[];
        surfaces: Option[];
        sorts: Option[];
    };
}>();

const { criteria, hasFilters, search, setNear, reset } = useVenueSearch(
    props.criteria,
);

/**
 * Native selects speak strings; the criteria speak enums or null. The value
 * is only ever one of the offered options, so the narrowing cast is safe.
 */
function enumOrNull<T extends string>(
    value: string,
    allowed: Option[],
): T | null {
    return allowed.some((option) => option.value === value)
        ? (value as T)
        : null;
}

const courtType = computed({
    get: () => criteria.courtType ?? '',
    set: (value: string) => {
        criteria.courtType = enumOrNull<CourtType>(
            value,
            props.options.courtTypes,
        );
    },
});

const wallType = computed({
    get: () => criteria.wallType ?? '',
    set: (value: string) => {
        criteria.wallType = enumOrNull<WallType>(
            value,
            props.options.wallTypes,
        );
    },
});

const surface = computed({
    get: () => criteria.surface ?? '',
    set: (value: string) => {
        criteria.surface = enumOrNull<Surface>(value, props.options.surfaces);
    },
});

const sort = computed({
    get: () => criteria.sort,
    set: (value: string) => {
        criteria.sort =
            enumOrNull<VenueSort>(value, props.options.sorts) ?? 'score';
    },
});

const term = computed({
    get: () => criteria.term ?? '',
    set: (value: string) => {
        criteria.term = value.trim() === '' ? null : value;
    },
});

const city = computed({
    get: () => criteria.city ?? '',
    set: (value: string) => {
        criteria.city = value.trim() === '' ? null : value;
    },
});

const minScore = computed({
    get: () => (criteria.minScore === null ? '' : String(criteria.minScore)),
    set: (value: string) => {
        criteria.minScore = value === '' ? null : Number(value);
    },
});

const latitude = ref(
    props.criteria.near ? String(props.criteria.near.latitude) : '',
);
const longitude = ref(
    props.criteria.near ? String(props.criteria.near.longitude) : '',
);
const radius = ref(String(props.criteria.radiusKm));

function submit(): void {
    const lat = latitude.value === '' ? null : Number(latitude.value);
    const lng = longitude.value === '' ? null : Number(longitude.value);
    setNear(lat, lng);
    criteria.radiusKm = radius.value === '' ? 25 : Number(radius.value);
    search();
}

function clear(): void {
    latitude.value = '';
    longitude.value = '';
    radius.value = '25';
    reset();
}
</script>

<template>
    <Head title="Venues" />

    <h1 class="text-3xl font-bold tracking-tight">Venues</h1>

    <form
        class="mt-6 grid gap-4 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-4"
        aria-label="Filter venues"
        @submit.prevent="submit"
    >
        <div class="grid gap-1.5 lg:col-span-2">
            <Label for="term">Search</Label>
            <Input
                id="term"
                v-model="term"
                type="search"
                placeholder="Venue or city"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="city">City</Label>
            <Input id="city" v-model="city" placeholder="e.g. Manchester" />
        </div>
        <div class="grid gap-1.5">
            <Label for="sort">Sort by</Label>
            <NativeSelect id="sort" v-model="sort" :options="options.sorts" />
        </div>

        <div class="grid gap-1.5">
            <Label for="court_type">Court type</Label>
            <NativeSelect
                id="court_type"
                v-model="courtType"
                :options="options.courtTypes"
                placeholder="Any"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="wall_type">Walls</Label>
            <NativeSelect
                id="wall_type"
                v-model="wallType"
                :options="options.wallTypes"
                placeholder="Any"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="surface">Surface</Label>
            <NativeSelect
                id="surface"
                v-model="surface"
                :options="options.surfaces"
                placeholder="Any"
            />
        </div>
        <div class="grid gap-1.5">
            <Label for="min_score">Minimum score</Label>
            <Input
                id="min_score"
                v-model="minScore"
                type="number"
                min="0"
                max="5"
                step="0.5"
            />
        </div>

        <div class="grid gap-1.5">
            <Label for="lat">Latitude</Label>
            <Input id="lat" v-model="latitude" type="number" step="any" />
        </div>
        <div class="grid gap-1.5">
            <Label for="lng">Longitude</Label>
            <Input id="lng" v-model="longitude" type="number" step="any" />
        </div>
        <div class="grid gap-1.5">
            <Label for="radius">Within (km)</Label>
            <Input
                id="radius"
                v-model="radius"
                type="number"
                min="1"
                max="200"
            />
        </div>
        <div class="flex items-end gap-2">
            <Button type="submit" class="flex-1">Search</Button>
            <Button
                v-if="hasFilters"
                type="button"
                variant="ghost"
                @click="clear"
            >
                Clear
            </Button>
        </div>
    </form>

    <p class="text-muted-foreground mt-6 text-sm" role="status">
        {{ venues.total }} {{ venues.total === 1 ? 'venue' : 'venues' }} found
    </p>

    <ul class="mt-4 space-y-3">
        <li v-for="venue in venues.data" :key="venue.id">
            <VenueCard :venue="venue" />
        </li>
    </ul>

    <div class="mt-6">
        <PaginationLinks :links="venues.links" />
    </div>
</template>
