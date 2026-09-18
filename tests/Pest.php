<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

function expectUniqueViolation(Closure $statement): void
{
    expectSqlState('23505', $statement);
}

function expectCheckViolation(Closure $statement): void
{
    expectSqlState('23514', $statement);
}
