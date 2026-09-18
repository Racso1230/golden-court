<?php

declare(strict_types=1);

namespace App\Http\Requests\Discovery;

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Venues\Data\VenueSearchCriteria;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\ValueObjects\Coordinates;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the search query string and turns it into domain criteria.
 */
class VenueSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    public function rules(): array
    {
        return [
            'term' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius' => ['nullable', 'integer', 'between:1,200'],
            'court_type' => ['nullable', Rule::enum(CourtType::class)],
            'wall_type' => ['nullable', Rule::enum(WallType::class)],
            'surface' => ['nullable', Rule::enum(Surface::class)],
            'min_score' => ['nullable', 'numeric', 'between:0,5'],
            'sort' => ['nullable', Rule::enum(VenueSort::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toCriteria(): VenueSearchCriteria
    {
        $near = $this->filled('lat') && $this->filled('lng')
            ? Coordinates::from($this->float('lat'), $this->float('lng'))
            : null;

        return new VenueSearchCriteria(
            term: $this->filled('term') ? $this->string('term')->trim()->toString() : null,
            city: $this->filled('city') ? $this->string('city')->trim()->toString() : null,
            near: $near,
            radiusKm: $this->filled('radius') ? $this->integer('radius') : VenueSearchCriteria::DEFAULT_RADIUS_KM,
            courtType: $this->enum('court_type', CourtType::class),
            wallType: $this->enum('wall_type', WallType::class),
            surface: $this->enum('surface', Surface::class),
            minScore: $this->filled('min_score') ? $this->float('min_score') : null,
            sort: $this->enum('sort', VenueSort::class) ?? VenueSort::Score,
            page: $this->filled('page') ? $this->integer('page') : 1,
        );
    }
}
