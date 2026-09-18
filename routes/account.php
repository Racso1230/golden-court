<?php

declare(strict_types=1);

use App\Http\Controllers\Account\ClaimIndexController;
use App\Http\Controllers\Account\ReviewIndexController;
use App\Http\Controllers\Notifications\MarkAllNotificationsReadController;
use App\Http\Controllers\Notifications\MarkNotificationReadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('account/reviews', ReviewIndexController::class)->name('account.reviews');
    Route::get('account/claims', ClaimIndexController::class)->name('account.claims');

    Route::post('notifications/read-all', MarkAllNotificationsReadController::class)->name('notifications.read-all');
    Route::post('notifications/{notification}/read', MarkNotificationReadController::class)->name('notifications.read');
});
