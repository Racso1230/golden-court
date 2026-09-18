<?php

declare(strict_types=1);

namespace App\Http\Requests\Discovery;

use App\Domain\Reviews\Enums\ReviewSort;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CourtShowRequest extends FormRequest
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
            'sort' => ['nullable', Rule::enum(ReviewSort::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function sort(): ReviewSort
    {
        return $this->enum('sort', ReviewSort::class) ?? ReviewSort::Recent;
    }

    public function page(): int
    {
        return $this->filled('page') ? $this->integer('page') : 1;
    }
}
