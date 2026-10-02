<?php

declare(strict_types=1);

use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\View\ViewException;
use Inertia\Ssr\SsrException;
use PHPUnit\Framework\Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutExceptionHandling;

beforeEach(function (): void {
    Config::set('inertia.ssr.enabled', true);
    Config::set('inertia.ssr.ensure_bundle_exists', false);
});

it('renders public pages through the SSR gateway', function (): void {
    Http::fake([
        '*' => Http::response([
            'head' => ['<title data-inertia="title">Rendered on the server</title>'],
            'body' => '<div id="app" data-server-rendered="true">rendered</div>',
        ]),
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('data-server-rendered="true"', false)
        ->assertSee('Rendered on the server', false);

    Http::assertSentCount(1);
});

it('never sends private pages to the gateway', function (): void {
    Http::fake();

    get(route('login'))->assertOk();
    actingAs(User::factory()->create())->get(route('dashboard'))->assertOk();

    Http::assertNothingSent();
});

it('falls back to client rendering when the gateway is unreachable', function (): void {
    Http::fake(['*' => Http::failedConnection()]);

    get(route('home'))
        ->assertOk()
        ->assertSee('id="app"', false)
        ->assertDontSee('data-server-rendered', false);
});

it('fails loudly when configured to', function (): void {
    Config::set('inertia.ssr.throw_on_error', true);
    Http::fake(['*' => Http::failedConnection()]);
    withoutExceptionHandling();

    try {
        get(route('home'));
    } catch (ViewException $exception) {
        // The gateway is called while the root view renders, so Blade wraps the failure.
        expect($exception->getPrevious())->toBeInstanceOf(SsrException::class);

        return;
    }

    Assert::fail('Expected the SSR failure to be thrown.');
});
