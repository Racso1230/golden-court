<?php

declare(strict_types=1);

use App\Domain\Reviews\Enums\ReviewSort;
use App\Domain\Shared\Data\OptionData;
use App\Domain\Venues\Enums\VenueSort;

it('turns a labelled enum into select options', function (): void {
    $options = OptionData::fromEnum(VenueSort::class);

    expect($options)->toHaveCount(4)
        ->and($options[0]->value)->toBe('score')
        ->and($options[0]->label)->toBe('Highest rated')
        ->and($options[2]->value)->toBe('distance')
        ->and($options[2]->label)->toBe('Nearest');
});

it('labels the review sort options', function (): void {
    expect(array_map(fn (OptionData $option): string => $option->label, OptionData::fromEnum(ReviewSort::class)))
        ->toBe(['Most recent', 'Most helpful', 'Highest rated', 'Lowest rated']);
});
