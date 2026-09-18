<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Casts;

use App\Domain\Reviews\ValueObjects\Rating;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Casts an integer column to and from a Rating value object.
 *
 * @implements CastsAttributes<Rating, Rating|int>
 */
final class RatingCast implements CastsAttributes
{
    /**
     * @param  mixed  $value  Raw column value; the interface fixes this as mixed.
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Rating
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value) && ! (is_string($value) && is_numeric($value))) {
            throw new InvalidArgumentException(sprintf('Column [%s] does not hold an integer rating.', $key));
        }

        return Rating::from((int) $value);
    }

    /**
     * @param  mixed  $value  A Rating, an int or null; the interface fixes this as mixed.
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Rating) {
            return $value->value;
        }

        if (is_int($value)) {
            return Rating::from($value)->value;
        }

        throw new InvalidArgumentException(sprintf('Attribute [%s] must be set to a Rating or an int.', $key));
    }
}
