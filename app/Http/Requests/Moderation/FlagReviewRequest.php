<?php

declare(strict_types=1);

namespace App\Http\Requests\Moderation;

use App\Domain\Moderation\Data\FlagReviewData;
use App\Domain\Moderation\Enums\FlagReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class FlagReviewRequest extends FormRequest
{
    /**
     * The controller checks the policy against the bound review.
     */
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
            'reason' => ['required', Rule::enum(FlagReason::class)],
            'details' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): FlagReviewData
    {
        return new FlagReviewData(
            reason: $this->enum('reason', FlagReason::class) ?? FlagReason::Other,
            details: $this->filled('details') ? $this->string('details')->trim()->toString() : null,
        );
    }
}
