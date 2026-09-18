<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The model lives outside App\Models, so name it explicitly.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'display_name' => fake()->unique()->userName(),
            'bio' => fake()->optional(0.4)->sentence(12),
            'role' => $this->weightedRole(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            // An established account: old enough to pass the minimum-age rule for reviewing.
            'created_at' => now()->subWeek(),
            'updated_at' => now()->subWeek(),
        ];
    }

    /**
     * An account created moments ago, which the anti-abuse rules hold back.
     */
    public function justRegistered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function player(): static
    {
        return $this->state(fn (array $attributes): array => ['role' => Role::Player]);
    }

    public function venueOwner(): static
    {
        return $this->state(fn (array $attributes): array => ['role' => Role::VenueOwner]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => ['role' => Role::Admin]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Nine in ten users are plain players.
     */
    private function weightedRole(): Role
    {
        if (fake()->boolean(90)) {
            return Role::Player;
        }

        return fake()->boolean(70) ? Role::VenueOwner : Role::Admin;
    }
}
