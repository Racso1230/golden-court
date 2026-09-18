<?php

declare(strict_types=1);

use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Models\Review;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retention windows live in config/golden_court.php; the models decide what is prunable.
Schedule::command('model:prune', ['--model' => [Review::class, ReviewFlag::class]])
    ->daily()
    ->at('03:30');
