import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { VenueSearchCriteria } from '@/types';

const routerGet = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    router: { get: (...args: unknown[]) => routerGet(...args) },
}));

vi.mock('@/routes/venues', () => ({
    index: Object.assign(() => ({ url: '/venues', method: 'get' }), {
        url: () => '/venues',
    }),
}));

const { useVenueSearch, DEFAULT_RADIUS_KM } =
    await import('@/composables/useVenueSearch');

function criteria(
    overrides: Partial<VenueSearchCriteria> = {},
): VenueSearchCriteria {
    return {
        term: null,
        city: null,
        near: null,
        radiusKm: DEFAULT_RADIUS_KM,
        courtType: null,
        wallType: null,
        surface: null,
        minScore: null,
        sort: 'score',
        page: 1,
        ...overrides,
    };
}

describe('useVenueSearch', () => {
    beforeEach(() => {
        routerGet.mockReset();
    });

    it('produces an empty query for default criteria', () => {
        const search = useVenueSearch(criteria());

        expect(search.toQuery()).toEqual({});
        expect(search.hasFilters.value).toBe(false);
    });

    it('maps criteria to the snake_case query the request validates', () => {
        const search = useVenueSearch(
            criteria({
                term: 'manchester',
                city: 'Manchester',
                near: { latitude: 53.48, longitude: -2.24 },
                radiusKm: 50,
                courtType: 'indoor',
                wallType: 'panoramic',
                surface: 'carpet',
                minScore: 4,
                sort: 'distance',
            }),
        );

        expect(search.toQuery()).toEqual({
            term: 'manchester',
            city: 'Manchester',
            lat: '53.48',
            lng: '-2.24',
            radius: '50',
            court_type: 'indoor',
            wall_type: 'panoramic',
            surface: 'carpet',
            min_score: '4',
            sort: 'distance',
        });
        expect(search.hasFilters.value).toBe(true);
    });

    it('omits the radius when there is no point and when it is the default', () => {
        expect(useVenueSearch(criteria({ radiusKm: 50 })).toQuery()).toEqual(
            {},
        );
        expect(
            useVenueSearch(
                criteria({ near: { latitude: 1, longitude: 2 } }),
            ).toQuery(),
        ).toEqual({ lat: '1', lng: '2' });
    });

    it('navigates with the query and preserves state and scroll', () => {
        const search = useVenueSearch(criteria({ term: 'padel' }));

        search.search();

        expect(routerGet).toHaveBeenCalledWith(
            '/venues',
            { term: 'padel' },
            { preserveState: true, preserveScroll: true },
        );
    });

    it('sets and clears the search point', () => {
        const search = useVenueSearch(criteria());

        search.setNear(51.5, -0.1);
        expect(search.criteria.near).toEqual({
            latitude: 51.5,
            longitude: -0.1,
        });

        search.setNear(null, -0.1);
        expect(search.criteria.near).toBeNull();
    });

    it('resets every filter and searches again', () => {
        const search = useVenueSearch(
            criteria({ term: 'x', minScore: 3, sort: 'name' }),
        );

        search.reset();

        expect(search.hasFilters.value).toBe(false);
        expect(search.criteria.sort).toBe('score');
        expect(routerGet).toHaveBeenCalledWith(
            '/venues',
            {},
            expect.anything(),
        );
    });
});
