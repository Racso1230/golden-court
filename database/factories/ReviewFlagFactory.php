<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewFlag>
 */
class ReviewFlagFactory extends Factory
{
    /**
     * @var class-string<ReviewFlag>
     */
    protected $model = ReviewFlag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory(),
            'reason' => fake()->randomElement(FlagReason::cases()),
            'details' => fake()->optional(0.5)->sentence(),
            'resolved_at' => null,
            'resolved_by_user_id' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'resolved_at' => now(),
            'resolved_by_user_id' => User::factory()->admin(),
        ]);
    }
}
