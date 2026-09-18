<?php

declare(strict_types=1);

use App\Domain\Moderation\Actions\RecordModerationLogAction;
use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Moderation\Models\ModerationLog;
use App\Domain\Moderation\SystemActor;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

it('records who did what to which subject', function (): void {
    $admin = User::factory()->admin()->create();

    $log = app(RecordModerationLogAction::class)->handle($admin, ModerationAction::ClaimApproved, ModerationSubject::Claim, 42, ['note' => 'looks legit']);

    expect($log->actor_user_id)->toBe($admin->id)
        ->and($log->actor_label)->toBe('admin:'.$admin->id)
        ->and($log->action)->toBe(ModerationAction::ClaimApproved)
        ->and($log->subject_type)->toBe(ModerationSubject::Claim)
        ->and($log->subject_id)->toBe(42)
        ->and($log->details)->toBe(['note' => 'looks legit'])
        ->and($log->created_at)->not->toBeNull()
        ->and($log->actor?->is($admin))->toBeTrue();
});

it('records system decisions without a user', function (): void {
    $log = app(RecordModerationLogAction::class)->handle(new SystemActor, ModerationAction::ReviewStatusChanged, ModerationSubject::Review, 7);

    expect($log->actor_user_id)->toBeNull()
        ->and($log->actor_label)->toBe('system')
        ->and($log->details)->toBeNull();
});

it('keeps the log when the acting admin is deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $log = app(RecordModerationLogAction::class)->handle($admin, ModerationAction::ClaimRejected, ModerationSubject::Claim, 1);

    $admin->delete();

    expect($log->refresh()->actor_user_id)->toBeNull()
        ->and($log->actor_label)->toBe('admin:'.$admin->id);
});

it('has no updated_at column, by design', function (): void {
    expect(ModerationLog::UPDATED_AT)->toBeNull()
        ->and(DB::getSchemaBuilder()->hasColumn('moderation_logs', 'updated_at'))->toBeFalse();
});

it('rejects an action outside the enum at the database', function (): void {
    expectCheckViolation(fn () => DB::table('moderation_logs')->insert([
        'actor_label' => 'system',
        'action' => 'shrugged',
        'subject_type' => 'review',
        'subject_id' => 1,
        'created_at' => now(),
    ]));
});
