<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * Everything the head of a page says about it. Built on the server by the
 * page's controller (or defaulted by the Inertia middleware), rendered to
 * HTML strings by HeadTagRenderer and sent to the client as the `head` prop.
 */
final readonly class PageMetaData
{
    public const int DESCRIPTION_MAX_LENGTH = 160;

    /**
     * @param  array<string, array<string, mixed>>  $jsonLd  Structured data blocks keyed by a stable name.
     */
    private function __construct(
        public string $title,
        public RobotsDirective $robots,
        public ?string $description,
        public ?string $canonical,
        public string $ogType,
        public ?OgImageData $image,
        public array $jsonLd,
        public bool $brandSuffix,
    ) {}

    /**
     * A public page: indexable, with a canonical URL and a description.
     */
    public static function indexable(string $title, string $description, string $canonical): self
    {
        return new self(
            title: self::clean($title),
            robots: RobotsDirective::Index,
            description: self::summarise($description),
            canonical: $canonical,
            ogType: 'website',
            image: null,
            jsonLd: [],
            brandSuffix: true,
        );
    }

    /**
     * A private page: crawlers are told to ignore it and nothing else is said.
     */
    public static function noindex(string $title): self
    {
        return new self(
            title: self::clean($title),
            robots: RobotsDirective::NoIndex,
            description: null,
            canonical: null,
            ogType: 'website',
            image: null,
            jsonLd: [],
            brandSuffix: true,
        );
    }

    public function withRobots(RobotsDirective $robots): self
    {
        return new self(
            $this->title,
            $robots,
            $this->description,
            $this->canonical,
            $this->ogType,
            $this->image,
            $this->jsonLd,
            $this->brandSuffix,
        );
    }

    /**
     * For pages that must not be treated as a copy of anything, such as
     * filtered search results.
     */
    public function withoutCanonical(): self
    {
        return new self(
            $this->title,
            $this->robots,
            $this->description,
            null,
            $this->ogType,
            $this->image,
            $this->jsonLd,
            $this->brandSuffix,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withJsonLd(string $key, array $data): self
    {
        return new self(
            $this->title,
            $this->robots,
            $this->description,
            $this->canonical,
            $this->ogType,
            $this->image,
            [...$this->jsonLd, $key => $data],
            $this->brandSuffix,
        );
    }

    public function withImage(OgImageData $image): self
    {
        return new self(
            $this->title,
            $this->robots,
            $this->description,
            $this->canonical,
            $this->ogType,
            $image,
            $this->jsonLd,
            $this->brandSuffix,
        );
    }

    /**
     * For titles that already name the site, such as the home page.
     */
    public function withoutBrandSuffix(): self
    {
        return new self(
            $this->title,
            $this->robots,
            $this->description,
            $this->canonical,
            $this->ogType,
            $this->image,
            $this->jsonLd,
            false,
        );
    }

    private static function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Collapses whitespace and cuts at a word boundary so the description
     * never exceeds what search engines display.
     */
    private static function summarise(string $text): string
    {
        $text = self::clean($text);

        if (mb_strlen($text) <= self::DESCRIPTION_MAX_LENGTH) {
            return $text;
        }

        $cut = mb_substr($text, 0, self::DESCRIPTION_MAX_LENGTH - 1);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim($lastSpace === false ? $cut : mb_substr($cut, 0, $lastSpace), ' ,;:.').'…';
    }
}
