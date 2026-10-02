<?php

declare(strict_types=1);

namespace App\Domain\Venues\Data;

use App\Domain\Venues\Models\Venue;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A venue's postal address, for pages that show a court in the context of
 * its venue and for structured data.
 */
#[TypeScript]
final class PostalAddressData extends Data
{
    public function __construct(
        public string $line1,
        public ?string $line2,
        public string $city,
        public string $postcode,
        public string $countryCode,
    ) {}

    public static function fromModel(Venue $venue): self
    {
        return new self(
            line1: $venue->address_line_1,
            line2: $venue->address_line_2,
            city: $venue->city,
            postcode: $venue->postcode,
            countryCode: $venue->country_code,
        );
    }
}
