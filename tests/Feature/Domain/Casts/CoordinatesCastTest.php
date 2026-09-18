<?php

declare(strict_types=1);

use App\Domain\Venues\Models\Venue;
use App\Domain\Venues\ValueObjects\Coordinates;
use Illuminate\Support\Facades\DB;

it('reads latitude and longitude into a Coordinates object', function (): void {
    $venue = Venue::factory()->create(['latitude' => 53.4808, 'longitude' => -2.2426]);

    $coordinates = Venue::query()->whereKey($venue->id)->firstOrFail()->coordinates;

    expect($coordinates)->toBeInstanceOf(Coordinates::class)
        ->and($coordinates?->latitude)->toBe(53.4808)
        ->and($coordinates?->longitude)->toBe(-2.2426);
});

it('writes both columns when coordinates are set', function (): void {
    $venue = Venue::factory()->create(['latitude' => 53.4808, 'longitude' => -2.2426]);

    $venue->coordinates = Coordinates::from(51.5074, -0.1278);
    $venue->save();

    $row = DB::table('venues')->where('id', $venue->id);

    expect((float) $row->value('latitude'))->toBe(51.5074)
        ->and((float) $row->value('longitude'))->toBe(-0.1278);
});

it('accepts coordinates through mass assignment on create', function (): void {
    $venue = Venue::factory()->create(['coordinates' => Coordinates::from(55.9533, -3.1883)]);

    expect($venue->latitude)->toBe(55.9533)
        ->and($venue->longitude)->toBe(-3.1883);
});
