<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * @var class-string<Venue>
     */
    protected $model = Venue::class;

    /**
     * UK cities with their approximate centre, used to keep generated venues
     * inside the country and clustered like real ones.
     *
     * @var list<array{city: string, latitude: float, longitude: float}>
     */
    public const array CITIES = [
        ['city' => 'London', 'latitude' => 51.5074, 'longitude' => -0.1278],
        ['city' => 'Manchester', 'latitude' => 53.4808, 'longitude' => -2.2426],
        ['city' => 'Birmingham', 'latitude' => 52.4862, 'longitude' => -1.8904],
        ['city' => 'Leeds', 'latitude' => 53.8008, 'longitude' => -1.5491],
        ['city' => 'Bristol', 'latitude' => 51.4545, 'longitude' => -2.5879],
        ['city' => 'Edinburgh', 'latitude' => 55.9533, 'longitude' => -3.1883],
        ['city' => 'Glasgow', 'latitude' => 55.8642, 'longitude' => -4.2518],
        ['city' => 'Liverpool', 'latitude' => 53.4084, 'longitude' => -2.9916],
        ['city' => 'Newcastle upon Tyne', 'latitude' => 54.9783, 'longitude' => -1.6178],
        ['city' => 'Cardiff', 'latitude' => 51.4816, 'longitude' => -3.1791],
        ['city' => 'Brighton', 'latitude' => 50.8225, 'longitude' => -0.1372],
        ['city' => 'Nottingham', 'latitude' => 52.9548, 'longitude' => -1.1581],
        ['city' => 'Sheffield', 'latitude' => 53.3811, 'longitude' => -1.4701],
        ['city' => 'Oxford', 'latitude' => 51.7520, 'longitude' => -1.2577],
        ['city' => 'Cambridge', 'latitude' => 52.2053, 'longitude' => 0.1218],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var array{city: string, latitude: float, longitude: float} $place */
        $place = fake()->randomElement(self::CITIES);

        return [
            'name' => sprintf(
                '%s %s',
                fake()->randomElement(['Riverside', 'Parkside', 'Northgate', 'Ironworks', 'Harbour', 'Meadow', 'Hilltop', 'Old Mill', 'Quayside', 'Kings Road', 'Station', 'Castle']),
                fake()->randomElement(['Padel Club', 'Padel Centre', 'Padel Arena', 'Racket Club', 'Padel Hub']),
            ),
            'description' => fake()->optional(0.7)->paragraph(),
            'address_line_1' => sprintf('%s %s', fake()->buildingNumber(), fake('en_GB')->streetName()),
            'address_line_2' => fake()->boolean(30) ? sprintf('Unit %d', fake()->numberBetween(1, 20)) : null,
            'city' => $place['city'],
            'postcode' => fake('en_GB')->postcode(),
            'country_code' => 'GB',
            // Scatter within roughly five kilometres of the city centre.
            'latitude' => round($place['latitude'] + fake()->randomFloat(6, -0.045, 0.045), 6),
            'longitude' => round($place['longitude'] + fake()->randomFloat(6, -0.07, 0.07), 6),
            'website' => fake()->optional(0.6)->url(),
            'phone' => fake('en_GB')->optional(0.6)->phoneNumber(),
        ];
    }

    public function claimedBy(User $owner): static
    {
        return $this->state(fn (array $attributes): array => ['claimed_by_user_id' => $owner->id]);
    }
}
