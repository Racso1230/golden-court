<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contracts;

/**
 * A backed enum whose cases have a human-readable label for the UI.
 */
interface HasLabel
{
    public function label(): string;
}
