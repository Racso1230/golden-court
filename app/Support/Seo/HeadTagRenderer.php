<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * Turns page metadata into head elements as HTML strings. The strings travel
 * as the `head` Inertia prop: the SSR renderer and the client head manager
 * print them, and the Blade root view prints the same list when SSR is off,
 * so every path produces identical markup.
 *
 * Every element carries a data-inertia key so the client can replace it on
 * navigation and adopt it unchanged on hydration. Values are escaped here;
 * callers pass plain text.
 */
final readonly class HeadTagRenderer
{
    public function __construct(private Site $site) {}

    /**
     * @return list<string>
     */
    public function render(PageMetaData $meta): array
    {
        $title = $meta->brandSuffix
            ? sprintf('%s | %s', $meta->title, $this->site->name)
            : $meta->title;

        $tags = [
            sprintf('<title data-inertia="title">%s</title>', e($title)),
            $this->meta('robots', $meta->robots->value),
        ];

        if ($meta->description !== null) {
            $tags[] = $this->meta('description', $meta->description);
        }

        if ($meta->robots->isPublic()) {
            if ($meta->canonical !== null) {
                $tags[] = sprintf('<link rel="canonical" href="%s" data-inertia="canonical">', e($meta->canonical));
            }

            array_push($tags, ...$this->openGraph($meta));
        }

        foreach ($meta->jsonLd as $key => $data) {
            $tags[] = sprintf(
                '<script type="application/ld+json" data-inertia="ld:%s">%s</script>',
                e($key),
                JsonLd::encode($data),
            );
        }

        return $tags;
    }

    /**
     * @return list<string>
     */
    private function openGraph(PageMetaData $meta): array
    {
        $image = $meta->image ?? $this->site->image;

        $tags = [
            $this->property('og:site_name', $this->site->name),
            $this->property('og:locale', $this->site->ogLocale),
            $this->property('og:type', $meta->ogType),
            $this->property('og:title', $meta->title),
        ];

        if ($meta->description !== null) {
            $tags[] = $this->property('og:description', $meta->description);
        }

        if ($meta->canonical !== null) {
            $tags[] = $this->property('og:url', $meta->canonical);
        }

        if ($image !== null) {
            $tags[] = $this->property('og:image', $image->url);
            $tags[] = $this->property('og:image:width', (string) $image->width);
            $tags[] = $this->property('og:image:height', (string) $image->height);
            $tags[] = $this->property('og:image:alt', $image->alt);
        }

        $tags[] = $this->meta('twitter:card', $image === null ? 'summary' : 'summary_large_image');
        $tags[] = $this->meta('twitter:title', $meta->title);

        if ($meta->description !== null) {
            $tags[] = $this->meta('twitter:description', $meta->description);
        }

        if ($image !== null) {
            $tags[] = $this->meta('twitter:image', $image->url);
        }

        if ($this->site->twitterSite !== null) {
            $tags[] = $this->meta('twitter:site', $this->site->twitterSite);
        }

        return $tags;
    }

    private function meta(string $name, string $content): string
    {
        return sprintf('<meta name="%s" content="%s" data-inertia="%s">', e($name), e($content), e($name));
    }

    private function property(string $property, string $content): string
    {
        return sprintf('<meta property="%s" content="%s" data-inertia="%s">', e($property), e($content), e($property));
    }
}
