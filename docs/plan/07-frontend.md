# 07 — Frontend

Goal: replace the minimal pages with a coherent Vue/TypeScript UI where every prop type is generated from the PHP Data classes. The point is type safety across the stack, not visual polish — keep the design clean and consistent using the starter kit's shadcn-vue components.

## Tasks

### 7.1 Generated types
- Install `spatie/laravel-typescript-transformer`. Configure it to collect `App\Domain\**\Data\*` classes (annotate them with `#[TypeScript]`) and PHP enums, writing to `resources/js/types/generated.d.ts`.
- Add `"types": "php artisan typescript:transform"` to `package.json` and run it before `type-check` in `npm run check`.
- Commit the generated file (this is the one exception in `CLAUDE.md`) so CI can type-check without PHP if needed — but also run the transformer in CI and fail if the committed file is stale (`git diff --exit-code`).
- Delete any hand-written types that duplicate generated ones.

Commit: `Generate TypeScript types from PHP Data classes`

### 7.2 Shared layout and components
- `AppLayout.vue`: header with search box, auth links, flash messages.
- `RatingStars.vue`: display-only, props `value: number` (0–5, one decimal) and `size`.
- `RatingInput.vue`: 1–5 selectable, `v-model` typed as `Rating` (number literal union `1 | 2 | 3 | 4 | 5`).
- `ScoreBadge.vue`: aggregate score + review count, handles the zero-review state.
- `GoldenCourtBadge.vue`.
- All components use `<script setup lang="ts">` with `defineProps<...>()` referencing generated types where applicable.

Commit: `Add shared layout and rating components`

### 7.3 Discovery pages
- `Venues/Index.vue`: filter panel (term, city, court type, wall type, surface, min score, sort), results as cards, pagination. Filters bound to the URL via Inertia router `get` with `preserveState`. Use a composable `useVenueSearch()` that owns the criteria state and is typed with `VenueSearchCriteria`.
- `Venues/Show.vue`: venue header, court grid, claim button (if unclaimed and logged in), owner badge.
- `Courts/Show.vue`: per-dimension averages as bars, sort control, review list, "write a review" CTA that routes to the form or shows why not (already reviewed / not logged in / not verified).

Commit: `Build discovery pages`

### 7.4 Review pages and components
- `ReviewCard.vue`: scores, body, author, date, helpful button (optimistic UI with rollback on error), flag button opening a small dialog, owner reply block if present.
- `Reviews/Create.vue` and `Reviews/Edit.vue` share `ReviewForm.vue` using Inertia `useForm<SubmitReviewFormData>()`; errors displayed per field.
- `ReplyForm.vue` inline on the review card for owners.

Commit: `Build review components and forms`

### 7.5 Account pages
- `Profile/Reviews.vue`: the user's own reviews with edit/delete.
- `Profile/Claims.vue`: their claims and statuses.
- Notifications dropdown in the header reading database notifications; mark-as-read endpoint.

Commit: `Add account pages`

### 7.6 Admin pages
Replace the Phase 6 minimal admin pages with tables (shadcn-vue data table) and confirm dialogs for destructive actions.

Commit: `Build admin pages`

### 7.7 Frontend tests
- Vitest with `@vue/test-utils` for `RatingInput`, `RatingStars`, `ScoreBadge`, and the `useVenueSearch` composable.
- Add `"test": "vitest run"` to `npm run check`.

Commit: `Add component and composable tests`

### 7.8 Accessibility pass
- Rating inputs operable by keyboard with ARIA labels.
- Colour is never the only carrier of meaning (scores show numbers).
- Run axe (browser extension or `vitest-axe`) on the main pages and fix findings.

Commit: `Accessibility fixes`

## Acceptance criteria

- [ ] Renaming a property on any PHP Data class and running `npm run check` fails type-checking until the Vue code is updated.
- [ ] No `any` in `resources/js` without an explanatory comment.
- [ ] Every page receives typed props; no page reaches into `usePage().props` untyped.
- [ ] Vitest suite passes and runs in CI.
- [ ] `composer check` and `npm run check` pass.
