<?php

declare(strict_types=1);

namespace App\Http\Requests\Reviews;

use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\ValueObjects\Rating;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class SubmitReviewRequest extends FormRequest
{
    /**
     * Authorisation needs the court, so the controller checks the policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mirrors the database constraints so users get a friendly message instead of a 500.
     *
     * @return array<string, array<int, ValidationRule|Stringable|string>>
     */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer', Rule::exists('courts', 'id')->whereNull('deleted_at')],
            ...self::ratingRules(),
            'body' => ['required', 'string', 'min:20', 'max:2000'],
            'played_on' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function toData(): SubmitReviewData
    {
        return new SubmitReviewData(
            courtId: $this->integer('court_id'),
            glass: $this->integer('glass'),
            lighting: $this->integer('lighting'),
            turf: $this->integer('turf'),
            facilities: $this->integer('facilities'),
            body: $this->string('body')->toString(),
            playedOn: $this->filled('played_on') ? CarbonImmutable::parse($this->string('played_on')->toString()) : null,
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function ratingRules(): array
    {
        $rule = ['required', 'integer', sprintf('min:%d', Rating::MIN), sprintf('max:%d', Rating::MAX)];

        return [
            'glass' => $rule,
            'lighting' => $rule,
            'turf' => $rule,
            'facilities' => $rule,
        ];
    }
}
