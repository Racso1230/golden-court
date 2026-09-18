<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;

it('rejects a duplicate slug within the same venue', function (): void {
    $venue = Venue::factory()->create();
    Court::factory()->for($venue)->create(['slug' => 'court-1']);

    expectUniqueViolation(fn () => Court::factory()->for($venue)->create(['slug' => 'court-1']));
});

it('allows the same slug at different venues', function (): void {
    Court::factory()->create(['slug' => 'court-1']);
    Court::factory()->create(['slug' => 'court-1']);

    expect(Court::query()->where('slug', 'court-1')->count())->toBe(2);
});

it('rejects a court type, wall type or surface outside its enum', function (string $column): void {
    $court = Court::factory()->create();

    expectCheckViolation(fn () => DB::table('courts')->where('id', $court->id)->update([$column => 'bogus']));
})->with(['court_type', 'wall_type', 'surface']);

it('rejects an aggregate score outside 0..5', function (): void {
    $court = Court::factory()->create();

    expectCheckViolation(fn () => DB::table('courts')->where('id', $court->id)->update(['aggregate_score' => -0.1]));
});

it('cascades deletion from the venue', function (): void {
    $court = Court::factory()->create();

    DB::table('venues')->where('id', $court->venue_id)->delete();

    expect(DB::table('courts')->where('id', $court->id)->exists())->toBeFalse();
});
