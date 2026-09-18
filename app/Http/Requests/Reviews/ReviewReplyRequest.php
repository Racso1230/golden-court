<?php

declare(strict_types=1);

namespace App\Http\Requests\Reviews;

use App\Domain\Reviews\Data\ReplyToReviewData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewReplyRequest extends FormRequest
{
    /**
     * The controller checks the policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mirrors the 1–1000 character CHECK on review_replies.body.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    public function toData(): ReplyToReviewData
    {
        return new ReplyToReviewData(body: $this->string('body')->trim()->toString());
    }
}
