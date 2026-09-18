<?php

declare(strict_types=1);

namespace App\Domain\Venues\Casts;

use App\Domain\Venues\ValueObjects\Coordinates;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Maps a virtual `coordinates` attribute onto the `latitude` and `longitude`
 * columns.
 *
 * There is no `coordinates` column, so `$value` passed to get() is always
 * null and the object is built from the sibling attributes instead. On set(),
 * returning an associative array tells Eloquent to write each key as its own
 * column, which is how a single cast can populate two columns.
 *
 * @implements CastsAttributes<Coordinates, array{latitude: float, longitude: float}|array{latitude: null, longitude: null}>
 */
final class CoordinatesCast implements CastsAttributes
{
    /**
     * @param  mixed  $value  Always null (virtual attribute); the interface fixes this as mixed.
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Coordinates
    {
        $latitude = $attributes['latitude'] ?? null;
        $longitude = $attributes['longitude'] ?? null;

        if ($latitude === null || $longitude === null) {
            return null;
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            throw new InvalidArgumentException('Columns [latitude] and [longitude] must be numeric.');
        }

        return Coordinates::from((float) $latitude, (float) $longitude);
    }

    /**
     * @param  mixed  $value  A Coordinates instance or null; the interface fixes this as mixed.
     * @param  array<string, mixed>  $attributes
     * @return array{latitude: float, longitude: float}|array{latitude: null, longitude: null}
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return ['latitude' => null, 'longitude' => null];
        }

        if (! $value instanceof Coordinates) {
            throw new InvalidArgumentException(sprintf('Attribute [%s] must be set to a Coordinates instance.', $key));
        }

        return [
            'latitude' => $value->latitude,
            'longitude' => $value->longitude,
        ];
    }
}
