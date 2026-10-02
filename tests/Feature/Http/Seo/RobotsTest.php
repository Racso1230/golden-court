<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('points crawlers at the sitemap and away from private areas', function (): void {
    $response = get(route('robots'))->assertOk();
    $body = (string) $response->getContent();

    expect($response->headers->get('Content-Type'))->toStartWith('text/plain')
        ->and($body)->toStartWith("User-agent: *\n")
        ->and($body)->toContain("Disallow: /admin\n")
        ->and($body)->toContain("Disallow: /account\n")
        ->and($body)->toContain("Disallow: /*?*lat=\n")
        ->and($body)->toContain(sprintf('Sitemap: %s/sitemap.xml', rtrim((string) config('app.url'), '/')));
});

it('keeps the venue listings and the login page crawlable', function (): void {
    $body = (string) get(route('robots'))->getContent();

    expect($body)->not->toContain('Disallow: /venues')
        ->and($body)->not->toContain('Disallow: /login')
        ->and($body)->not->toContain('Disallow: /register');
});
