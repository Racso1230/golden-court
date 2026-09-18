<?php

declare(strict_types=1);

use App\Http\Controllers\Claims\StoreVenueClaimController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'throttle:10,1'])->group(function (): void {
    Route::post('venues/{venue:slug}/claims', StoreVenueClaimController::class)->name('venues.claims.store');
});
