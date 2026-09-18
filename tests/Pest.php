<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests hit HTTP and the database, so they extend the Laravel
| TestCase and refresh the PostgreSQL test database between tests. Unit
| tests are plain PHP and need neither.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Database constraint helpers
|--------------------------------------------------------------------------
|
| Constraints are asserted on the SQLSTATE PostgreSQL reports, never on the
| wording of the error message.
|
*/

/**
 * Run a statement that PostgreSQL must reject with the given SQLSTATE.
 */
function expectSqlState(string $sqlState, Closure $statement): void
{
    try {
        $statement();
    } catch (QueryException $exception) {
        expect($exception->getCode())->toBe($sqlState);

        return;
    }

    Assert::fail(sprintf('Expected the database to reject the statement with SQLSTATE %s, but it succeeded.', $sqlState));
}

/**
 * Run an artisan command through the console test harness. The plain helper is
 * typed as returning an int too, which is never the case with mocked output.
 *
 * @param  array<string, mixed>  $parameters
 */
function runArtisan(string $command, array $parameters = []): PendingCommand
{
    $pending = \Pest\Laravel\artisan($command, $parameters);

    if (! $pending instanceof PendingCommand) {
        throw new LogicException('Expected a PendingCommand; is console output mocking disabled?');
    }

    return $pending;
}

function expectUniqueViolation(Closure $statement): void
{
    expectSqlState('23505', $statement);
}

function expectCheckViolation(Closure $statement): void
{
    expectSqlState('23514', $statement);
}
