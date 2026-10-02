<?php

declare(strict_types=1);

use App\Support\Seo\OgImageData;
use App\Support\Seo\Site;
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

/*
|--------------------------------------------------------------------------
| Head tag helpers
|--------------------------------------------------------------------------
|
| Pages send their head elements as HTML strings in the `head` prop, each
| keyed by a data-inertia attribute (see app/Support/Seo/HeadTagRenderer).
|
*/

/**
 * The head element with the given data-inertia key, or null when absent.
 *
 * @param  array<mixed>  $head
 */
function headTag(array $head, string $key): ?string
{
    foreach ($head as $tag) {
        if (is_string($tag) && str_contains($tag, sprintf('data-inertia="%s"', $key))) {
            return $tag;
        }
    }

    return null;
}

/**
 * The decoded JSON-LD block with the given key.
 *
 * @param  array<mixed>  $head
 * @return array<mixed>
 */
function jsonLd(array $head, string $key): array
{
    $tag = headTag($head, 'ld:'.$key);

    if ($tag === null) {
        Assert::fail(sprintf('No JSON-LD block "%s" in the head.', $key));
    }

    preg_match('/>(.*)<\/script>$/s', $tag, $matches);
    $decoded = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decoded)) {
        Assert::fail(sprintf('JSON-LD block "%s" is not an object.', $key));
    }

    return $decoded;
}

/**
 * A site for unit tests of the SEO support classes.
 */
function seoSite(?OgImageData $image = null): Site
{
    return new Site(
        name: 'Golden Court',
        baseUrl: 'https://golden-court.test',
        description: 'Padel court reviews by players.',
        locale: 'en-GB',
        ogLocale: 'en_GB',
        image: $image,
    );
}
