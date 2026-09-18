<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Contracts;

/**
 * Decides whether a review body is acceptable. Implementations may be as
 * simple as a few heuristics or as involved as an external classifier; the
 * submission flow only sees a reason or null.
 */
interface ReviewContentRule
{
    /**
     * A human-readable reason the body is rejected, or null when it passes.
     */
    public function violation(string $body): ?string;
}
