<?php

declare(strict_types=1);

namespace App\Http\Requests\Reviews;

use App\Domain\Reviews\Data\UpdateReviewData;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Stringable;

class UpdateReviewRequest extends FormRequest
{
    /**
     * The controller checks the policy against the bound review.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        return [
            ...SubmitReviewRequest::ratingRules(),
            'body' => ['required', 'string', 'min:20', 'max:2000'],
            'played_on' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function toData(): UpdateReviewData
    {
        return new UpdateReviewData(
            glass: $this->integer('glass'),
            lighting: $this->integer('lighting'),
            turf: $this->integer('turf'),
            facilities: $this->integer('facilities'),
            body: $this->string('body')->toString(),
            playedOn: $this->filled('played_on') ? CarbonImmutable::parse($this->string('played_on')->toString()) : null,
        );
    }
}
