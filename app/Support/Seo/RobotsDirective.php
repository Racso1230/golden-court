<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * What crawlers may do with a page. The value is the robots meta content.
 */
enum RobotsDirective: string
{
    case Index = 'index, follow';
    case NoIndexFollow = 'noindex, follow';
    case NoIndex = 'noindex, nofollow';

    /**
     * Whether the page is meant to be shared and so carries a canonical URL
     * and Open Graph tags. A search variant that is noindex,follow still does;
     * a private page does not.
     */
    public function isPublic(): bool
    {
        return $this !== self::NoIndex;
    }

    public function isIndexable(): bool
    {
        return $this === self::Index;
    }
}
