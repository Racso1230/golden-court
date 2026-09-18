<?php

declare(strict_types=1);

namespace App\Domain\Venues\Models;

use App\Domain\Courts\Models\Court;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Casts\CoordinatesCast;
use App\Domain\Venues\ValueObjects\Coordinates;
use Carbon\CarbonImmutable;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $postcode
 * @property string $country_code
 * @property float $latitude
 * @property float $longitude
 * @property Coordinates|null $coordinates
 * @property string|null $website
 * @property string|null $phone
 * @property float $aggregate_score
 * @property int $review_count
 * @property int|null $claimed_by_user_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read int|null $courts_count Present when queried with withCount('courts').
 */
#[Fillable([
    'name',
    'slug',
    'description',
    'address_line_1',
    'address_line_2',
    'city',
    'postcode',
    'country_code',
    'coordinates',
    'latitude',
    'longitude',
    'website',
    'phone',
])]
#[Hidden(['search_vector'])]
#[UseFactory(VenueFactory::class)]
class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Venue $venue): void {
            if (blank($venue->slug)) {
                $venue->slug = self::uniqueSlugFor($venue->name);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'coordinates' => CoordinatesCast::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'aggregate_score' => 'float',
            'review_count' => 'integer',
        ];
    }

    /**
     * @return HasMany<Court, $this>
     */
    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    /**
     * The user whose claim on this venue was approved, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function isClaimed(): bool
    {
        return $this->claimed_by_user_id !== null;
    }

    /**
     * Distance from the search point, in kilometres. Only present when the
     * venue was loaded by a geographic search that selected `distance_km`;
     * strict mode would throw on a plain property read, so check first.
     */
    public function distanceKm(): ?float
    {
        $raw = $this->getAttributes()['distance_km'] ?? null;

        return is_numeric($raw) ? (float) $raw : null;
    }

    private static function uniqueSlugFor(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = sprintf('%s-%d', $base, $suffix);
            $suffix++;
        }

        return $slug;
    }
}
