<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\Reviews\Enums\ReviewStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * An admin decision about a review: keep it live or take it down.
 */
class ReviewOutcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|In|string>>
     */
    public function rules(): array
    {
        return [
            'outcome' => ['required', Rule::in([ReviewStatus::Published->value, ReviewStatus::Removed->value])],
        ];
    }

    public function outcome(): ReviewStatus
    {
        return $this->enum('outcome', ReviewStatus::class) ?? ReviewStatus::Published;
    }
}
