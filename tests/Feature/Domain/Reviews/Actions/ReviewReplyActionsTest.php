<?php

declare(strict_types=1);

use App\Domain\Reviews\Actions\DeleteReviewReplyAction;
use App\Domain\Reviews\Actions\ReplyToReviewAction;
use App\Domain\Reviews\Actions\UpdateReviewReplyAction;
use App\Domain\Reviews\Data\ReplyToReviewData;
use App\Domain\Reviews\Events\ReviewReplied;
use App\Domain\Reviews\Exceptions\ReplyAlreadyExistsException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Event;

it('posts the owner reply and announces it', function (): void {
    Event::fake([ReviewReplied::class]);
    $review = Review::factory()->create();
    $owner = User::factory()->venueOwner()->create();

    $reply = app(ReplyToReviewAction::class)->handle($owner, $review, new ReplyToReviewData('Thanks for the feedback, the lights are being upgraded.'));

    expect($reply->review_id)->toBe($review->id)
        ->and($reply->user_id)->toBe($owner->id)
        ->and($review->refresh()->reply?->is($reply))->toBeTrue();

    Event::assertDispatched(ReviewReplied::class, fn (ReviewReplied $event): bool => $event->reviewId === $review->id && $event->replyId === $reply->id);
});

it('translates the one-reply-per-review index into a domain exception', function (): void {
    Event::fake([ReviewReplied::class]);
    $reply = ReviewReply::factory()->create();

    expect(fn () => app(ReplyToReviewAction::class)->handle($reply->user, $reply->review, new ReplyToReviewData('Another go.')))
        ->toThrow(ReplyAlreadyExistsException::class);

    expect(ReviewReply::query()->count())->toBe(1);
    Event::assertNotDispatched(ReviewReplied::class);
});

it('updates a reply body', function (): void {
    $reply = ReviewReply::factory()->create();

    app(UpdateReviewReplyAction::class)->handle($reply, new ReplyToReviewData('Edited reply.'));

    expect($reply->refresh()->body)->toBe('Edited reply.');
});

it('deletes a reply', function (): void {
    $reply = ReviewReply::factory()->create();

    app(DeleteReviewReplyAction::class)->handle($reply);

    expect(ReviewReply::query()->find($reply->id))->toBeNull();
});
