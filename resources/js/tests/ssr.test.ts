// @vitest-environment node
import type { Page, PageProps } from '@inertiajs/core';
import { createInertiaApp } from '@inertiajs/vue3';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { DefineComponent } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { resolveLayout } from '@/inertia';
import {
    courtDetail,
    options,
    paginated,
    recentReview,
    review,
    searchCriteria,
    sharedProps,
    venueDetail,
    venueSummary,
} from '@/tests/fixtures';

/**
 * Runs in a plain Node environment, like the SSR server does: no window, no
 * document, no navigator. Importing a module that touches a browser API at
 * evaluation time, or rendering a public page that does so during setup,
 * fails here before it fails in production.
 */

type PageModule = { default: DefineComponent };

const pages = import.meta.glob<PageModule>('../pages/**/*.vue');
const layouts = import.meta.glob<unknown>('../layouts/**/*.vue');
const components = import.meta.glob<unknown>('../components/*.vue');
const composables = import.meta.glob<unknown>('../composables/*.ts');
const lib = import.meta.glob<unknown>('../lib/*.ts');

const modules = Object.entries({
    ...pages,
    ...layouts,
    ...components,
    ...composables,
    ...lib,
}).filter(([path]) => !path.endsWith('.test.ts'));

let warnings: string[] = [];

function capture(...args: unknown[]): void {
    const [first] = args;

    warnings.push(
        typeof first === 'string' ? first : 'non-string console output',
    );
}

beforeEach(() => {
    warnings = [];
    vi.spyOn(console, 'warn').mockImplementation(capture);
    vi.spyOn(console, 'error').mockImplementation(capture);
});

afterEach(() => {
    vi.restoreAllMocks();
});

async function renderPage(
    component: string,
    props: Record<string, unknown>,
    url: string,
): Promise<{ head: string[]; body: string }> {
    const render = await createInertiaApp<PageProps>({
        resolve: (name) => {
            const importer = pages[`../pages/${name}.vue`];

            if (!importer) {
                throw new Error(`Unknown page component ${name}`);
            }

            return importer().then((module) => module.default);
        },
        layout: resolveLayout,
        serverHead: true,
        dev: false,
    });

    if (typeof render !== 'function') {
        throw new Error('Expected createInertiaApp to return the SSR renderer');
    }

    const page: Page<PageProps> = {
        component,
        props: { ...sharedProps(), ...props, errors: {} },
        url,
        version: null,
        rescuedProps: [],
        flash: {},
        rememberedState: {},
    };

    return render(page, renderToString);
}

describe('server-side rendering', () => {
    it.each(modules)(
        'imports %s without touching browser APIs',
        async (_path, importer) => {
            await expect(importer()).resolves.toBeDefined();
            expect(warnings).toEqual([]);
        },
    );

    it('renders the home page', async () => {
        const { head, body } = await renderPage(
            'Home',
            { topVenues: [venueSummary()], recentReviews: [recentReview()] },
            '/',
        );

        expect(body).toContain('data-server-rendered="true"');
        expect(body).toContain('Find the best padel courts near you');
        expect(body).toContain('Harbourside Padel');
        expect(head.some((tag) => tag.startsWith('<title'))).toBe(true);
        expect(warnings).toEqual([]);
    });

    it('renders the venue index', async () => {
        const { body } = await renderPage(
            'Venues/Index',
            {
                venues: paginated([venueSummary()]),
                criteria: searchCriteria({ city: 'Bristol' }),
                options: {
                    courtTypes: options({
                        indoor: 'Indoor',
                        outdoor: 'Outdoor',
                    }),
                    wallTypes: options({ panoramic: 'Panoramic' }),
                    surfaces: options({ artificial_grass: 'Artificial grass' }),
                    sorts: options({ score: 'Highest rated', name: 'Name' }),
                },
            },
            '/venues?city=Bristol',
        );

        expect(body).toContain('data-server-rendered="true"');
        expect(body).toContain('Harbourside Padel');
        expect(warnings).toEqual([]);
    });

    it('renders a venue page', async () => {
        const { body } = await renderPage(
            'Venues/Show',
            { venue: venueDetail(), canClaim: false },
            '/venues/harbourside-padel',
        );

        expect(body).toContain('data-server-rendered="true"');
        expect(body).toContain('Harbourside Padel');
        expect(body).toContain('Court 1');
        expect(warnings).toEqual([]);
    });

    it('renders a court page with its reviews', async () => {
        const { body } = await renderPage(
            'Courts/Show',
            {
                court: courtDetail(),
                reviews: paginated([review()]),
                sort: 'recent',
                sortOptions: options({
                    recent: 'Most recent',
                    highest: 'Highest',
                }),
                flagReasons: options({ spam: 'Spam', other: 'Other' }),
                canReview: false,
                reviewableFrom: null,
                hasReviewed: false,
                canReply: false,
            },
            '/venues/harbourside-padel/courts/court-1',
        );

        expect(body).toContain('data-server-rendered="true"');
        expect(body).toContain('Court 1');
        expect(body).toContain('the turf is a little worn');
        expect(body).toContain('20 Sept 2026');
        expect(warnings).toEqual([]);
    });
});
