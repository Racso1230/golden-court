<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ApproveVenueClaimController;
use App\Http\Controllers\Admin\ChangeReviewStatusController;
use App\Http\Controllers\Admin\ClaimIndexController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlagIndexController;
use App\Http\Controllers\Admin\PendingReviewIndexController;
use App\Http\Controllers\Admin\RejectVenueClaimController;
use App\Http\Controllers\Admin\ResolveReviewFlagsController;
use App\Http\Controllers\Admin\ReviewShowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('claims', ClaimIndexController::class)->name('claims.index');
        Route::post('claims/{claim}/approve', ApproveVenueClaimController::class)->name('claims.approve');
        Route::post('claims/{claim}/reject', RejectVenueClaimController::class)->name('claims.reject');

        Route::get('flags', FlagIndexController::class)->name('flags.index');

        Route::get('reviews/pending', PendingReviewIndexController::class)->name('reviews.pending');
        Route::get('reviews/{review}', ReviewShowController::class)->name('reviews.show');
        Route::post('reviews/{review}/resolve-flags', ResolveReviewFlagsController::class)->name('reviews.resolve-flags');
        Route::post('reviews/{review}/status', ChangeReviewStatusController::class)->name('reviews.status');
    });
