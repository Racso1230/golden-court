<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;

it('labels every court type', function (): void {
    expect(CourtType::Indoor->label())->toBe('Indoor')
        ->and(CourtType::Outdoor->label())->toBe('Outdoor')
        ->and(CourtType::Covered->label())->toBe('Covered');
});

it('labels every wall type', function (): void {
    expect(WallType::Panoramic->label())->toBe('Panoramic')
        ->and(WallType::Classic->label())->toBe('Classic');
});

it('labels every surface', function (): void {
    expect(Surface::ArtificialGrass->label())->toBe('Artificial grass')
        ->and(Surface::Carpet->label())->toBe('Carpet')
        ->and(Surface::Concrete->label())->toBe('Concrete')
        ->and(Surface::Other->label())->toBe('Other');
});

it('uses snake_case backing values', function (): void {
    expect(Surface::ArtificialGrass->value)->toBe('artificial_grass')
        ->and(CourtType::Covered->value)->toBe('covered')
        ->and(WallType::Panoramic->value)->toBe('panoramic');
});
