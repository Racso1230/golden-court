<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewReply>
 */
class ReviewReplyFactory extends Factory
{
    /**
     * @var class-string<ReviewReply>
     */
    protected $model = ReviewReply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory()->venueOwner(),
            // The schema requires 1–1000 characters.
            'body' => fake()->paragraph(fake()->numberBetween(1, 5)),
        ];
    }
}
