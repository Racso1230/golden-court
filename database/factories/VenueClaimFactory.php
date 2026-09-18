<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueClaim>
 */
class VenueClaimFactory extends Factory
{
    /**
     * @var class-string<VenueClaim>
     */
    protected $model = VenueClaim::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'user_id' => User::factory(),
            'status' => ClaimStatus::Pending,
            'evidence' => fake()->paragraph(),
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ClaimStatus::Approved,
            'reviewed_by_user_id' => User::factory()->admin(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ClaimStatus::Rejected,
            'reviewed_by_user_id' => User::factory()->admin(),
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
