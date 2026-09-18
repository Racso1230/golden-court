<?php

declare(strict_types=1);

use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;

it('rejects a duplicate slug', function (): void {
    Venue::factory()->create(['slug' => 'riverside-padel']);

    expectUniqueViolation(fn () => Venue::factory()->create(['slug' => 'riverside-padel']));
});

it('rejects a latitude outside -90..90', function (float $latitude): void {
    expectCheckViolation(fn () => Venue::factory()->create(['latitude' => $latitude]));
})->with([90.000001, -90.000001]);

it('rejects a longitude outside -180..180', function (float $longitude): void {
    expectCheckViolation(fn () => Venue::factory()->create(['longitude' => $longitude]));
})->with([180.000001, -180.000001]);

it('rejects an aggregate score outside 0..5', function (): void {
    $venue = Venue::factory()->create();

    expectCheckViolation(fn () => DB::table('venues')->where('id', $venue->id)->update(['aggregate_score' => 5.1]));
});

it('rejects a negative review count', function (): void {
    $venue = Venue::factory()->create();

    expectCheckViolation(fn () => DB::table('venues')->where('id', $venue->id)->update(['review_count' => -1]));
});

it('maintains a full-text search vector over name and city', function (): void {
    $venue = Venue::factory()->create(['name' => 'Harbourside Padel', 'city' => 'Bristol']);

    $matches = DB::table('venues')
        ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", ['bristol harbourside'])
        ->pluck('id');

    expect($matches->all())->toBe([$venue->id]);
});
