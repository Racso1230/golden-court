<?php

declare(strict_types=1);

use App\Domain\Venues\Exceptions\InvalidCoordinatesException;
use App\Domain\Venues\ValueObjects\Coordinates;

it('accepts the latitude and longitude boundaries', function (): void {
    expect(Coordinates::from(-90.0, -180.0)->latitude)->toBe(-90.0)
        ->and(Coordinates::from(90.0, 180.0)->longitude)->toBe(180.0)
        ->and(Coordinates::from(0.0, 0.0)->equals(Coordinates::from(0.0, 0.0)))->toBeTrue();
});

it('rejects a latitude outside -90..90', function (): void {
    expect(fn (): Coordinates => Coordinates::from(90.001, 0.0))
        ->toThrow(InvalidCoordinatesException::class, 'Latitude')
        ->and(fn (): Coordinates => Coordinates::from(-90.001, 0.0))
        ->toThrow(InvalidCoordinatesException::class, 'Latitude');
});

it('rejects a longitude outside -180..180', function (): void {
    expect(fn (): Coordinates => Coordinates::from(0.0, 180.001))
        ->toThrow(InvalidCoordinatesException::class, 'Longitude')
        ->and(fn (): Coordinates => Coordinates::from(0.0, -180.001))
        ->toThrow(InvalidCoordinatesException::class, 'Longitude');
});

it('measures the distance from Manchester to London at roughly 262 km', function (): void {
    $manchester = Coordinates::from(53.4808, -2.2426);
    $london = Coordinates::from(51.5074, -0.1278);

    expect($manchester->distanceToInKm($london))->toBeGreaterThan(260.0)->toBeLessThan(264.0)
        ->and($london->distanceToInKm($manchester))->toEqualWithDelta($manchester->distanceToInKm($london), 0.0001);
});

it('measures zero distance to itself', function (): void {
    $point = Coordinates::from(40.4168, -3.7038);

    expect($point->distanceToInKm($point))->toBe(0.0);
});

it('serialises latitude and longitude', function (): void {
    expect(json_encode(Coordinates::from(51.5, -0.1)))->toBe('{"latitude":51.5,"longitude":-0.1}');
});
