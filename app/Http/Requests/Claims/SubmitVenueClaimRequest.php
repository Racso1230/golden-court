<?php

declare(strict_types=1);

namespace App\Http\Requests\Claims;

use App\Domain\Claims\Data\SubmitVenueClaimData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitVenueClaimRequest extends FormRequest
{
    /**
     * The controller checks the policy against the bound venue.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'evidence' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    public function toData(): SubmitVenueClaimData
    {
        return new SubmitVenueClaimData(evidence: $this->string('evidence')->trim()->toString());
    }
}
