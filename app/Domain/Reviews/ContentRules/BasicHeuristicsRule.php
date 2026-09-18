<?php

declare(strict_types=1);

namespace App\Domain\Reviews\ContentRules;

use App\Domain\Reviews\Contracts\ReviewContentRule;

/**
 * Catches the two laziest kinds of junk: bodies that are mostly links, and
 * bodies that are one or two characters mashed repeatedly.
 */
final class BasicHeuristicsRule implements ReviewContentRule
{
    /**
     * Share of the body that may be URLs before it counts as link spam.
     */
    private const float MAX_URL_RATIO = 0.5;

    /**
     * Bodies whose non-space characters are drawn from this few distinct
     * characters are not a review.
     */
    private const int MIN_DISTINCT_CHARACTERS = 3;

    public function violation(string $body): ?string
    {
        if ($this->urlRatio($body) > self::MAX_URL_RATIO) {
            return 'Reviews cannot be mostly links.';
        }

        if ($this->distinctCharacters($body) < self::MIN_DISTINCT_CHARACTERS) {
            return 'Please write a few words about the court.';
        }

        return null;
    }

    private function urlRatio(string $body): float
    {
        $length = mb_strlen(trim($body));

        if ($length === 0) {
            return 0.0;
        }

        preg_match_all('~(?:https?://|www\.)\S+~iu', $body, $matches);

        $urlLength = array_sum(array_map(mb_strlen(...), $matches[0]));

        return $urlLength / $length;
    }

    private function distinctCharacters(string $body): int
    {
        $compact = preg_replace('/\s+/u', '', mb_strtolower($body)) ?? '';

        return count(array_unique(mb_str_split($compact)));
    }
}
