<?php

declare(strict_types=1);

use App\Http\Controllers\Moderation\FlagReviewController;
use App\Http\Controllers\Reviews\CreateReviewController;
use App\Http\Controllers\Reviews\DestroyReviewController;
use App\Http\Controllers\Reviews\DestroyReviewReplyController;
use App\Http\Controllers\Reviews\EditReviewController;
use App\Http\Controllers\Reviews\StoreReviewController;
use App\Http\Controllers\Reviews\StoreReviewReplyController;
use App\Http\Controllers\Reviews\ToggleReviewVoteController;
use App\Http\Controllers\Reviews\UpdateReviewController;
use App\Http\Controllers\Reviews\UpdateReviewReplyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('courts/{court}/reviews/create', CreateReviewController::class)->name('reviews.create');
    Route::get('reviews/{review}/edit', EditReviewController::class)->name('reviews.edit');

    // Named limiters live in RateLimitServiceProvider.
    Route::post('reviews', StoreReviewController::class)
        ->middleware('throttle:reviews')
        ->name('reviews.store');

    Route::post('reviews/{review}/flags', FlagReviewController::class)
        ->middleware('throttle:flags')
        ->name('reviews.flags.store');

    Route::post('reviews/{review}/vote', ToggleReviewVoteController::class)
        ->middleware('throttle:votes')
        ->name('reviews.vote');

    Route::middleware('throttle:30,1')->group(function (): void {
        Route::patch('reviews/{review}', UpdateReviewController::class)->name('reviews.update');
        Route::delete('reviews/{review}', DestroyReviewController::class)->name('reviews.destroy');

        Route::post('reviews/{review}/reply', StoreReviewReplyController::class)->name('reviews.reply.store');
        Route::patch('reviews/{review}/reply', UpdateReviewReplyController::class)->name('reviews.reply.update');
        Route::delete('reviews/{review}/reply', DestroyReviewReplyController::class)->name('reviews.reply.destroy');
    });
});
