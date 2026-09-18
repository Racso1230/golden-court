<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\ValueObjects\CourtScores;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Everything a player provides when reviewing a court.
 */
final class SubmitReviewData extends Data
{
    public function __construct(
        public int $courtId,
        public int $glass,
        public int $lighting,
        public int $turf,
        public int $facilities,
        public string $body,
        public ?CarbonImmutable $playedOn = null,
    ) {}

    public function scores(): CourtScores
    {
        return CourtScores::fromInts($this->glass, $this->lighting, $this->turf, $this->facilities);
    }
}
