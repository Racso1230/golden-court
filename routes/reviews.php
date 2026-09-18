<?php

declare(strict_types=1);

use App\Http\Controllers\Moderation\FlagReviewController;
use App\Http\Controllers\Reviews\CreateReviewController;
use App\Http\Controllers\Reviews\DestroyReviewController;
use App\Http\Controllers\Reviews\DestroyReviewReplyController;
use App\Http\Controllers\Reviews\StoreReviewController;
use App\Http\Controllers\Reviews\StoreReviewReplyController;
use App\Http\Controllers\Reviews\ToggleReviewVoteController;
use App\Http\Controllers\Reviews\UpdateReviewController;
use App\Http\Controllers\Reviews\UpdateReviewReplyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('courts/{court}/reviews/create', CreateReviewController::class)->name('reviews.create');

    // TODO (Phase 8): replace with the named `throttle:reviews` limiter.
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('reviews', StoreReviewController::class)->name('reviews.store');
        Route::patch('reviews/{review}', UpdateReviewController::class)->name('reviews.update');
        Route::delete('reviews/{review}', DestroyReviewController::class)->name('reviews.destroy');

        Route::post('reviews/{review}/flags', FlagReviewController::class)->name('reviews.flags.store');

        Route::post('reviews/{review}/reply', StoreReviewReplyController::class)->name('reviews.reply.store');
        Route::patch('reviews/{review}/reply', UpdateReviewReplyController::class)->name('reviews.reply.update');
        Route::delete('reviews/{review}/reply', DestroyReviewReplyController::class)->name('reviews.reply.destroy');
    });

    Route::post('reviews/{review}/vote', ToggleReviewVoteController::class)
        ->middleware('throttle:60,1')
        ->name('reviews.vote');
});
