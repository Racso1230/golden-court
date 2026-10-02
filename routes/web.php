<?php

declare(strict_types=1);

use App\Http\Controllers\Account\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/seo.php';
require __DIR__.'/discovery.php';
require __DIR__.'/settings.php';
require __DIR__.'/reviews.php';
require __DIR__.'/claims.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';
