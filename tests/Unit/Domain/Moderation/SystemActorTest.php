<?php

declare(strict_types=1);

use App\Domain\Moderation\Contracts\ModerationActor;
use App\Domain\Moderation\SystemActor;

it('is an anonymous actor that may moderate', function (): void {
    $actor = new SystemActor;

    expect($actor)->toBeInstanceOf(ModerationActor::class)
        ->and($actor->moderatorId())->toBeNull()
        ->and($actor->canModerate())->toBeTrue()
        ->and($actor->moderatorLabel())->toBe('system');
});
