<?php

declare(strict_types=1);

namespace App\Support\Health;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;

/**
 * Laravel's /up endpoint only proves PHP is answering. Running a trivial
 * query while it diagnoses health makes a lost database connection surface
 * as a failed health check instead of a green one.
 */
final class CheckDatabaseConnection
{
    public function handle(DiagnosingHealth $event): void
    {
        DB::select('select 1');
    }
}
