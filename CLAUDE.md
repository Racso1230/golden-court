# Golden Court — project conventions

Golden Court is a Trustpilot-style review hub for padel courts. Players rate individual courts at a venue across several dimensions; venue owners can claim a venue and reply to reviews. There is **no booking, pricing or availability** functionality and none should be added.

This file is the standing brief for Claude Code. Read `docs/plan/00-overview.md` before starting any phase, then the phase file you have been asked to work on. Work through one phase at a time and stop at the end of it.

## Stack

- PHP 8.4, Laravel 12, PostgreSQL 17 (PostGIS available)
- Inertia 2 + Vue 3 + TypeScript (Laravel Vue starter kit), Tailwind, shadcn-vue
- Pest for tests, Larastan (PHPStan) level 8, Pint, ESLint, vue-tsc
- spatie/laravel-data for DTOs, spatie/typescript-transformer for generated TS types
- Local dev via Laravel Herd; database on 127.0.0.1:5432, database name `golden_court`

## Non-negotiable code rules

1. `declare(strict_types=1);` at the top of every PHP file.
2. Every parameter, return and property is typed. No `mixed` unless unavoidable and commented.
3. Larastan level 8 must pass with zero errors. Never add `@phpstan-ignore` without an explanatory comment.
4. Controllers are thin: validate via a Form Request, build a Data object, call an Action, return a response. No business logic in controllers or models.
5. Business operations live in **Action** classes with a single public `handle()` method.
6. Anything that could plausibly be swapped is behind an **interface** in a `Contracts` namespace and bound in a service provider.
7. Domain concepts with rules (ratings, coordinates, scores) are **readonly value objects** that validate in their constructor and throw a domain exception.
8. Enums are backed PHP enums, never string constants.
9. Database constraints (unique, check, foreign key) are the last line of defence and are always tested.
10. Tests run against PostgreSQL, never SQLite.
11. TypeScript is `strict: true`. Frontend types for backend data come from `resources/js/types/generated.d.ts` — never hand-write a type that mirrors a PHP Data class.
12. No `any` in TypeScript without a comment explaining why.

## Folder layout

```
app/
  Domain/
    <BoundedContext>/          e.g. Reviews, Venues, Courts, Claims, Users
      Actions/
      Contracts/
      Data/
      Enums/
      Events/
      Exceptions/
      Listeners/
      Models/
      Policies/
      Queries/                 query builder objects / scopes
      ValueObjects/
  Http/
    Controllers/
    Requests/
    Middleware/
  Providers/
  Support/                     genuinely cross-cutting helpers only
```

Eloquent models live in their bounded context, not in `app/Models`. Update `config/auth.php` and any factory namespaces accordingly.

## Testing rules

- Pest. Feature tests for HTTP + database behaviour, unit tests for value objects, actions and aggregators.
- Every Action has at least one test. Every value object has tests for its boundaries.
- Every DB constraint has a test proving it fires.
- Use factories for all models. Factories must produce valid data by default.
- Name tests as sentences: `it('rejects a second review from the same user on the same court')`.

## Git conventions

- Small commits, one concern each, imperative mood: `Add Rating value object`, `Enforce review rating range at database level`.
- Do not commit generated TypeScript types unless the phase says to.
- Never commit `.env`.

## Definition of done for any task

`composer check` passes (Pint, PHPStan, Pest) and `npm run check` passes (ESLint, vue-tsc, build). If either script does not exist yet, Phase 1 creates them.

## What not to do

- Do not add booking, payments, availability, calendars or real-time features.
- Do not add packages beyond those named in the phase files without asking.
- Do not skip a phase's acceptance criteria to move faster.
- Do not put logic in Blade or Vue that belongs in a PHP Action or value object.
