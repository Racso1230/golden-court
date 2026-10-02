<script setup lang="ts">
import { LocateFixed, SearchX, SlidersHorizontal } from '@lucide/vue';
import { computed, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
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
        // Sorting re-runs the search straight away; the other filters wait for Search.
        submit();
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

// Filters fold away on small screens; open by default when some are applied.
const filtersOpen = ref(hasFilters.value);
const activeFilterCount = computed(
    () =>
        [
            criteria.term,
            criteria.city,
            criteria.courtType,
            criteria.wallType,
            criteria.surface,
            criteria.minScore,
            criteria.near,
        ].filter((value) => value !== null).length,
);

const locating = ref(false);
const locationError = ref<string | null>(null);

// Browser only: navigator is touched inside the click handler, never in setup.
function useMyLocation(): void {
    if (!('geolocation' in navigator)) {
        locationError.value = 'Your browser cannot share its location.';
        return;
    }

    locating.value = true;
    locationError.value = null;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            locating.value = false;
            latitude.value = position.coords.latitude.toFixed(4);
            longitude.value = position.coords.longitude.toFixed(4);
            criteria.sort = 'distance';
            submit();
        },
        () => {
            locating.value = false;
            locationError.value = 'We could not get your location.';
        },
        { timeout: 10000 },
    );
}
</script>

<template>
    <PageHeader
        title="Padel venues"
        description="Every venue, scored court by court by the people who play there."
    />

    <div class="lg:grid lg:grid-cols-[18rem_1fr] lg:items-start lg:gap-8">
        <aside class="mb-6 lg:sticky lg:top-24 lg:mb-0">
            <Button
                type="button"
                variant="outline"
                class="w-full lg:hidden"
                :aria-expanded="filtersOpen"
                aria-controls="venue-filters"
                @click="filtersOpen = !filtersOpen"
            >
                <SlidersHorizontal aria-hidden="true" />
                Filters
                <span
                    v-if="activeFilterCount > 0"
                    class="bg-gold-100 text-gold-900 rounded-full px-2 text-xs font-semibold tabular-nums"
                    >{{ activeFilterCount }}</span
                >
            </Button>

            <form
                id="venue-filters"
                class="bg-card mt-3 space-y-5 rounded-xl border p-5 shadow-xs lg:mt-0 lg:block"
                :class="{ hidden: !filtersOpen }"
                aria-label="Filter venues"
                @submit.prevent="submit"
            >
                <div class="grid gap-1.5">
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
                    <Input
                        id="city"
                        v-model="city"
                        placeholder="e.g. Manchester"
                    />
                </div>

                <fieldset class="border-t pt-5">
                    <!-- Floated so the legend sits inside the box, not on its border. -->
                    <legend
                        class="float-left mb-4 w-full text-sm font-semibold"
                    >
                        Courts
                    </legend>
                    <div class="clear-left space-y-4">
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
                                placeholder="Any"
                            />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t pt-5">
                    <!-- Floated so the legend sits inside the box, not on its border. -->
                    <legend
                        class="float-left mb-4 w-full text-sm font-semibold"
                    >
                        Near a place
                    </legend>
                    <div class="clear-left space-y-4">
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full"
                            :disabled="locating"
                            @click="useMyLocation"
                        >
                            <LocateFixed aria-hidden="true" />
                            {{ locating ? 'Finding you…' : 'Use my location' }}
                        </Button>
                        <p
                            v-if="locationError"
                            class="text-destructive text-sm"
                            role="alert"
                        >
                            {{ locationError }}
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-1.5">
                                <Label for="lat">Latitude</Label>
                                <Input
                                    id="lat"
                                    v-model="latitude"
                                    type="number"
                                    step="any"
                                />
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="lng">Longitude</Label>
                                <Input
                                    id="lng"
                                    v-model="longitude"
                                    type="number"
                                    step="any"
                                />
                            </div>
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
                    </div>
                </fieldset>

                <div class="flex gap-2 border-t pt-5">
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
        </aside>

        <section aria-labelledby="results-heading">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 id="results-heading" class="sr-only">Results</h2>
                <p class="text-muted-foreground text-sm" role="status">
                    <span class="text-foreground font-semibold tabular-nums">{{
                        venues.total
                    }}</span>
                    {{ venues.total === 1 ? 'venue' : 'venues' }} found
                </p>
                <div class="flex items-center gap-2">
                    <Label for="sort" class="text-muted-foreground"
                        >Sort by</Label
                    >
                    <div class="w-44">
                        <NativeSelect
                            id="sort"
                            v-model="sort"
                            :options="options.sorts"
                        />
                    </div>
                </div>
            </div>

            <EmptyState
                v-if="venues.data.length === 0"
                title="No venues match"
                description="Try a wider search, a bigger radius or fewer court filters."
            >
                <template #icon><SearchX /></template>
                <Button v-if="hasFilters" variant="outline" @click="clear">
                    Clear filters
                </Button>
            </EmptyState>

            <ul v-else class="grid gap-4 sm:grid-cols-2">
                <li v-for="venue in venues.data" :key="venue.id">
                    <VenueCard :venue="venue" />
                </li>
            </ul>

            <div class="mt-8">
                <PaginationLinks :links="venues.links" />
            </div>
        </section>
    </div>
</template>
