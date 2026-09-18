<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    /**
     * @var class-string<Court>
     */
    protected $model = Court::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'name' => sprintf('Court %d', fake()->numberBetween(1, 12)),
            'court_type' => fake()->randomElement(CourtType::cases()),
            'wall_type' => fake()->randomElement(WallType::cases()),
            'surface' => fake()->randomElement(Surface::cases()),
        ];
    }
}
