import type {
    CourtDetail,
    CourtSummary,
    Option,
    Paginated,
    RecentReview,
    Review,
    VenueDetail,
    VenueSearchCriteria,
    VenueSummary,
} from '@/types';

/**
 * Typed page-prop fixtures for component and SSR tests. Every factory
 * returns a value that satisfies the generated backend type, so a change to
 * a PHP Data class fails type-checking here before it fails at runtime.
 */

export function sharedProps(
    overrides: Record<string, unknown> = {},
): Record<string, unknown> {
    return {
        name: 'Golden Court',
        auth: { user: null },
        notifications: null,
        sidebarOpen: true,
        head: [
            '<title data-inertia="title">Golden Court</title>',
            '<meta name="robots" content="noindex, nofollow" data-inertia="robots">',
        ],
        ...overrides,
    };
}

export function venueSummary(
    overrides: Partial<VenueSummary> = {},
): VenueSummary {
    return {
        id: 1,
        name: 'Harbourside Padel',
        slug: 'harbourside-padel',
        city: 'Bristol',
        aggregateScore: 4.3,
        reviewCount: 12,
        courtCount: 3,
        distanceKm: null,
        hasGoldenCourt: true,
        ...overrides,
    };
}

export function courtSummary(
    overrides: Partial<CourtSummary> = {},
): CourtSummary {
    return {
        id: 1,
        name: 'Court 1',
        slug: 'court-1',
        courtType: 'indoor',
        courtTypeLabel: 'Indoor',
        wallType: 'panoramic',
        wallTypeLabel: 'Panoramic',
        surface: 'artificial_grass',
        surfaceLabel: 'Artificial grass',
        aggregateScore: 4.5,
        reviewCount: 8,
        isGoldenCourt: false,
        ...overrides,
    };
}

export function venueDetail(overrides: Partial<VenueDetail> = {}): VenueDetail {
    return {
        id: 1,
        name: 'Harbourside Padel',
        slug: 'harbourside-padel',
        description: 'Four indoor courts by the water.',
        addressLine1: '1 Harbour Way',
        addressLine2: null,
        city: 'Bristol',
        postcode: 'BS1 4AA',
        countryCode: 'GB',
        latitude: 51.45,
        longitude: -2.6,
        website: 'https://harbourside.example',
        phone: '0117 000 0000',
        aggregateScore: 4.3,
        reviewCount: 12,
        courtCount: 1,
        ownerDisplayName: 'club_owner',
        courts: [courtSummary()],
        ...overrides,
    };
}

export function courtDetail(overrides: Partial<CourtDetail> = {}): CourtDetail {
    return {
        court: courtSummary(),
        venueId: 1,
        venueName: 'Harbourside Padel',
        venueSlug: 'harbourside-padel',
        venueCity: 'Bristol',
        averages: { glass: 4.5, lighting: 4.2, turf: 4.8, facilities: 3.9 },
        ...overrides,
    };
}

export function review(overrides: Partial<Review> = {}): Review {
    return {
        id: 1,
        courtId: 1,
        scores: { glass: 5, lighting: 4, turf: 4, facilities: 4 },
        overall: 4.25,
        body: 'Great glass, good lights, the turf is a little worn.',
        status: 'published',
        authorDisplayName: 'padel_pat',
        playedOn: '2026-09-18',
        createdAt: '2026-09-20T10:00:00+00:00',
        updatedAt: '2026-09-20T10:00:00+00:00',
        helpfulCount: 3,
        hasVoted: false,
        isAuthor: false,
        reply: null,
        ...overrides,
    };
}

export function recentReview(
    overrides: Partial<RecentReview> = {},
): RecentReview {
    return {
        id: 1,
        overall: 4.25,
        excerpt: 'Great glass, good lights, the turf is a little worn.',
        authorDisplayName: 'padel_pat',
        courtName: 'Court 1',
        courtSlug: 'court-1',
        venueName: 'Harbourside Padel',
        venueSlug: 'harbourside-padel',
        createdAt: '2026-09-20T10:00:00+00:00',
        ...overrides,
    };
}

export function paginated<T>(data: T[]): Paginated<T> {
    return {
        data,
        links: [],
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: data.length,
        from: data.length > 0 ? 1 : null,
        to: data.length > 0 ? data.length : null,
    };
}

export function searchCriteria(
    overrides: Partial<VenueSearchCriteria> = {},
): VenueSearchCriteria {
    return {
        term: null,
        city: null,
        near: null,
        radiusKm: 25,
        courtType: null,
        wallType: null,
        surface: null,
        minScore: null,
        sort: 'score',
        page: 1,
        ...overrides,
    };
}

export function options(labels: Record<string, string>): Option[] {
    return Object.entries(labels).map(([value, label]) => ({ value, label }));
}
