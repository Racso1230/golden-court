import { router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { index as venuesIndex } from '@/routes/venues';
import type { VenueSearchCriteria } from '@/types';

/**
 * The query-string shape VenueSearchRequest accepts.
 */
export type VenueSearchQuery = Partial<
    Record<
        | 'term'
        | 'city'
        | 'lat'
        | 'lng'
        | 'radius'
        | 'court_type'
        | 'wall_type'
        | 'surface'
        | 'min_score'
        | 'sort',
        string
    >
>;

export const DEFAULT_RADIUS_KM = 25;

/**
 * Owns the search criteria for the venue index. State is the backend's
 * VenueSearchCriteria; `toQuery` maps it to the URL the request validates.
 */
export function useVenueSearch(initial: VenueSearchCriteria) {
    const criteria = reactive<VenueSearchCriteria>({ ...initial });

    const hasFilters = computed(
        () =>
            criteria.term !== null ||
            criteria.city !== null ||
            criteria.near !== null ||
            criteria.courtType !== null ||
            criteria.wallType !== null ||
            criteria.surface !== null ||
            criteria.minScore !== null,
    );

    function toQuery(): VenueSearchQuery {
        const query: VenueSearchQuery = {};

        if (criteria.term) query.term = criteria.term;
        if (criteria.city) query.city = criteria.city;
        if (criteria.near) {
            query.lat = String(criteria.near.latitude);
            query.lng = String(criteria.near.longitude);
        }
        if (criteria.near && criteria.radiusKm !== DEFAULT_RADIUS_KM) {
            query.radius = String(criteria.radiusKm);
        }
        if (criteria.courtType) query.court_type = criteria.courtType;
        if (criteria.wallType) query.wall_type = criteria.wallType;
        if (criteria.surface) query.surface = criteria.surface;
        if (criteria.minScore !== null)
            query.min_score = String(criteria.minScore);
        if (criteria.sort !== 'score') query.sort = criteria.sort;

        return query;
    }

    function search(): void {
        router.get(venuesIndex.url(), toQuery(), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function setNear(latitude: number | null, longitude: number | null): void {
        criteria.near =
            latitude === null || longitude === null
                ? null
                : { latitude, longitude };
    }

    function reset(): void {
        criteria.term = null;
        criteria.city = null;
        criteria.near = null;
        criteria.radiusKm = DEFAULT_RADIUS_KM;
        criteria.courtType = null;
        criteria.wallType = null;
        criteria.surface = null;
        criteria.minScore = null;
        criteria.sort = 'score';
        criteria.page = 1;
        search();
    }

    return { criteria, hasFilters, toQuery, search, setNear, reset };
}
