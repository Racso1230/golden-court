<?php

declare(strict_types=1);

use App\Http\Controllers\Reviews\CreateReviewController;
use App\Http\Controllers\Reviews\DestroyReviewController;
use App\Http\Controllers\Reviews\StoreReviewController;
use App\Http\Controllers\Reviews\UpdateReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('courts/{court}/reviews/create', CreateReviewController::class)->name('reviews.create');

    // TODO (Phase 8): replace with the named `throttle:reviews` limiter.
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('reviews', StoreReviewController::class)->name('reviews.store');
        Route::patch('reviews/{review}', UpdateReviewController::class)->name('reviews.update');
        Route::delete('reviews/{review}', DestroyReviewController::class)->name('reviews.destroy');
    });
});
