# 09 — Redesign, server-side rendering and SEO

Goal: replace the starter kit's look with Golden Court's own white-and-gold design on a single top-navigation layout, and make the public pages genuinely indexable: server-rendered HTML, per-page head tags, structured data, a sitemap and a robots file. No product scope changes: still no booking, pricing or availability.

Decisions behind this phase (2026-10-02): white only (no dark mode, no appearance setting); gold primary buttons with dark text; Instrument Serif display headings over Instrument Sans body; everything in scope (public, account, admin, settings, auth); one branch per sub-phase, fast-forwarded into `main` after review.

Every commit keeps `composer check` and `npm run check` green. Generated TypeScript types (`resources/js/types/generated.d.ts`) are committed, as since 7.1; Wayfinder output and `bootstrap/ssr` are not.

## Tasks

### 9.1 Rails: SSR, SEO backend, dark mode removed (branch `redesign/a-rails`)
- Make `config/inertia.php` environment-driven with every package key and disable SSR in `phpunit.xml`, so Pest never contacts a rendering gateway even once a bundle exists or `npm run dev` is running.
- Remove the appearance setting, its middleware, route, page, composable, cookie and every `dark:` utility. Keep the `dark` custom variant pointing at a class nothing sets, so a stray `dark:` class can never follow the OS theme.
- Make existing components safe on the server: deterministic date formatting through `resources/js/lib/dates.ts`, no browser APIs during `setup`.
- Build the SSR bundle from `resources/js/app.ts` as part of `npm run check`; CI asserts it exists. Only the public pages render on the server (`$withoutSsr` denylist in `HandleInertiaRequests`).
- Vitest guard that imports every page, layout, component and composable in a Node environment and renders the public pages through Inertia's server renderer.
- Head tags are built in PHP (`app/Support/Seo`: `PageMetaData`, `HeadTagRenderer`, `JsonLd`, `Site`) and sent as the `head` prop that Inertia's `serverHead` option renders on the server and the client; the Blade root view prints the same strings when SSR is off. Private pages default to `noindex, nofollow`.
- Per-page meta builders as Actions for Home, the venue index, venue and court pages; JSON-LD (`SportsActivityLocation`, `AggregateRating`, `Review`, `BreadcrumbList`, `WebSite`); the court read model gains the venue address and coordinates.
- `GET /sitemap.xml` (cached, venues and courts, `lastmod`) and `GET /robots.txt` (dynamic, with the sitemap URL) replacing the static file.
- README, ARCHITECTURE.md and CLAUDE.md corrected for Laravel 13 / Inertia 3 / vite-plus and extended with the SSR and SEO sections.

Commits: `Add the redesign phase plan`, `Drive Inertia SSR configuration from the environment`, `Remove the appearance setting and dark mode`, `Make existing components safe to render on the server`, `Build the SSR bundle in the checks`, `Add SSR render guard tests`, `Render head tags on the server`, `Add page meta for the home page and venue index`, `Add venue page meta and JSON-LD`, `Expose venue location on the court read model`, `Add court page meta and JSON-LD`, `Serve sitemap.xml`, `Serve robots.txt dynamically`, `Document SSR and SEO`

### 9.2 Shell: tokens, brand, single layout (branch `redesign/b-shell`)
- White and gold tokens in `resources/css/app.css` with a named `gold` scale, `success` tokens and Instrument Serif as `font-display`; fonts through the Bunny helper in `vite.config.ts`.
- Rating stars, dimension bars, badges, inputs and status text move from hard-coded Tailwind colours to the tokens.
- Golden Court brand mark (a gold padel ball), favicons, Apple touch icon, Open Graph image and web manifest, rendered once from committed HTML sources with headless Chrome.
- Shared primitives: `Textarea`, `PageHeader` (the page's only `h1`), `EmptyState`, `StatCard`, `SectionCard`, `StatusBadge`, `useNotifications`.
- One `AppLayout` with a sticky header (brand, Venues, search, notifications, user menu), section tabs for the account and admin areas, a footer and the toaster; `AuthLayout` and the settings layout rewritten; the starter sidebar shell and unused shadcn components deleted.

Commits: `Replace the starter theme with white and gold tokens`, `Move rating components and status colours to the design tokens`, `Add the Golden Court brand mark, icons and Open Graph image`, `Add shared page primitives`, `Replace the sidebar shell with a top-navigation layout`, `Delete the starter sidebar shell`, `Redesign the auth layout`, `Redesign the settings layout`, `Add accessibility tests for the shell primitives`

### 9.3 Public pages (branch `redesign/c-public`)
- Home hero with the search front and centre, top venues and latest reviews.
- Venue search with a filter column, result count, sort and pagination; every criterion and the geolocation flow preserved.
- Venue page with header, facts, claim call to action and courts grid; court page with a score panel, dimension bars, review list and owner replies. Breadcrumbs match the `BreadcrumbList` JSON-LD.
- Lighthouse SEO and accessibility pass on the four page types.

Commits: `Redesign the home page`, `Redesign the venue search page`, `Redesign the venue page`, `Redesign the court page`, `Tune public pages from a Lighthouse pass`

### 9.4 Signed-in pages, cleanup, release (branch `redesign/d-signed-in`)
- Review form, account, admin, settings and auth pages on the new primitives; every form field, route helper and `data-test` attribute preserved.
- No `dark:`, `amber-`, `neutral-` or `zinc-` utilities left; unused components and types removed.
- README screenshots and feature list, ARCHITECTURE.md design-system note, CHANGELOG `v0.2.0`, tag.

Commits: `Redesign the review form pages`, `Redesign the account pages`, `Redesign the admin console`, `Redesign the settings pages`, `Redesign the auth pages`, `Remove leftover starter styling`, `Document the redesign and release v0.2.0`

## Acceptance criteria

- [ ] Public pages are served with `data-server-rendered="true"` when the SSR server runs, and render identically (with the same head tags) when it does not.
- [ ] `view-source` of the home, venue index, venue and court pages shows a unique title, description, canonical, Open Graph tags and valid JSON-LD; private pages carry `noindex, nofollow`.
- [ ] `/sitemap.xml` lists every visible venue and court; `/robots.txt` points at it.
- [ ] No dark mode, no appearance setting, one layout for every signed-in and public page, exactly one `h1` per page.
- [ ] No browser console hydration warnings on any page; Lighthouse SEO and accessibility at 95 or above on the public pages.
- [ ] `composer check` and `npm run check` (which now builds the SSR bundle) pass; CI green; tagged `v0.2.0`.
