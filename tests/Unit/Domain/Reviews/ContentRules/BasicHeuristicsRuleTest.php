<?php

declare(strict_types=1);

use App\Domain\Reviews\ContentRules\BasicHeuristicsRule;

it('accepts an ordinary review', function (): void {
    expect((new BasicHeuristicsRule)->violation('Great glass, decent lights, the turf needs a top-up of sand.'))->toBeNull();
});

it('accepts a review that merely mentions a link', function (): void {
    expect((new BasicHeuristicsRule)->violation('Booked through https://example.test and the courts were superb, well lit and clean.'))->toBeNull();
});

it('rejects a body that is mostly links', function (): void {
    expect((new BasicHeuristicsRule)->violation('https://spam.example/buy-now http://spam.example/cheap-rackets ok'))
        ->toBe('Reviews cannot be mostly links.');
});

it('rejects a single repeated character', function (string $body): void {
    expect((new BasicHeuristicsRule)->violation($body))->toBe('Please write a few words about the court.');
})->with([
    str_repeat('a', 40),
    'aaaaaaaaaa!!!!!!!!!!!!!!!!!!!!!!!',
    str_repeat('ab ', 15),
]);
