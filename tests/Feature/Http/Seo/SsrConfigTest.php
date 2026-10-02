<?php

declare(strict_types=1);

it('never dispatches to a server-side rendering gateway while testing', function (): void {
    // phpunit.xml disables SSR: with a bundle on disk or `npm run dev` running,
    // every Inertia render would otherwise post the page to a rendering server.
    expect(config('inertia.ssr.enabled'))->toBeFalse()
        ->and(config('inertia.ssr.throw_on_error'))->toBeFalse();
});

it('uses the canonical site URL while testing', function (): void {
    expect(config('app.url'))->toBe('http://golden-court.test');
});
