<?php

declare(strict_types=1);

use App\Http\Controllers\Discovery\CourtShowController;
use App\Http\Controllers\Discovery\HomeController;
use App\Http\Controllers\Discovery\VenueIndexController;
use App\Http\Controllers\Discovery\VenueShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('venues', VenueIndexController::class)->name('venues.index');
Route::get('venues/{venue:slug}', VenueShowController::class)->name('venues.show');
Route::get('venues/{venue:slug}/courts/{court:slug}', CourtShowController::class)
    ->scopeBindings()
    ->name('courts.show');
