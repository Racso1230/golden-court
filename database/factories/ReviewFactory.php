<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\ValueObjects\Rating;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @var class-string<Review>
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'court_id' => Court::factory(),
            'glass_rating' => $this->rating(),
            'lighting_rating' => $this->rating(),
            'turf_rating' => $this->rating(),
            'facilities_rating' => $this->rating(),
            // The schema requires 20–2000 characters; a few lorem sentences sit comfortably inside.
            'body' => fake()->paragraph(fake()->numberBetween(2, 8)),
            'status' => ReviewStatus::Published,
            'played_on' => fake()->boolean(70) ? fake()->dateTimeBetween('-1 year', 'now') : null,
            'helpful_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => ReviewStatus::Pending]);
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => ReviewStatus::Flagged]);
    }

    public function removed(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => ReviewStatus::Removed]);
    }

    private function rating(): int
    {
        return fake()->numberBetween(Rating::MIN, Rating::MAX);
    }
}
