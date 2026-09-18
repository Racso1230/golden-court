<?php

declare(strict_types=1);

namespace App\Domain\Users\Data;

use Carbon\CarbonImmutable;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A database notification as the header menu shows it.
 */
#[TypeScript]
final class NotificationData extends Data
{
    public function __construct(
        public string $id,
        public string $message,
        public ?string $url,
        public ?CarbonImmutable $readAt,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromModel(DatabaseNotification $notification): self
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return new self(
            id: $notification->id,
            message: is_string($data['message'] ?? null) ? $data['message'] : 'You have a new notification.',
            url: self::urlFor($data),
            readAt: $notification->read_at === null ? null : CarbonImmutable::instance($notification->read_at),
            createdAt: $notification->created_at === null ? CarbonImmutable::now() : CarbonImmutable::instance($notification->created_at),
        );
    }

    /**
     * Every notification we send names a venue slug, and most a court slug too.
     *
     * @param  array<string, mixed>  $data
     */
    private static function urlFor(array $data): ?string
    {
        $venueSlug = $data['venue_slug'] ?? null;
        $courtSlug = $data['court_slug'] ?? null;

        if (! is_string($venueSlug)) {
            return null;
        }

        return is_string($courtSlug)
            ? route('courts.show', ['venue' => $venueSlug, 'court' => $courtSlug])
            : route('venues.show', ['venue' => $venueSlug]);
    }
}
