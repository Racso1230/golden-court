<?php

declare(strict_types=1);

use App\Domain\Moderation\Actions\RecordModerationLogAction;
use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Users\Models\User;
use App\Support\Health\CheckDatabaseConnection;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\get;

it('answers the health check when the database is reachable', function (): void {
    get('/up')->assertOk();
});

it('fails the health check when the database query fails', function (): void {
    DB::shouldReceive('select')->once()->with('select 1')->andThrow(new RuntimeException('connection refused'));

    expect(fn () => (new CheckDatabaseConnection)->handle(new DiagnosingHealth))->toThrow(RuntimeException::class);
});

it('mirrors every moderation action to the application log with structured context', function (): void {
    $spy = Log::spy();
    $admin = User::factory()->admin()->create();

    $log = app(RecordModerationLogAction::class)->handle($admin, ModerationAction::ClaimApproved, ModerationSubject::Claim, 42, ['venue_id' => 7]);

    $spy->shouldHaveReceived('info')->once()->with('moderation.action', [
        'action' => 'claim_approved',
        'actor' => 'admin:'.$admin->id,
        'actor_user_id' => $admin->id,
        'subject_type' => 'claim',
        'subject_id' => 42,
        'details' => ['venue_id' => 7],
        'moderation_log_id' => $log->id,
    ]);
});
