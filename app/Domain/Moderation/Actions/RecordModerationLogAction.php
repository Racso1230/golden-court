<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Actions;

use App\Domain\Moderation\Contracts\ModerationActor;
use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Moderation\Models\ModerationLog;
use Illuminate\Support\Facades\Log;

/**
 * The single writer of the audit trail. Called from inside the transaction
 * of the action being recorded so a log line never outlives a rolled-back
 * decision. Every entry is mirrored to the application log with the same
 * structured context so it shows up in log tooling too.
 */
final class RecordModerationLogAction
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function handle(
        ModerationActor $actor,
        ModerationAction $action,
        ModerationSubject $subjectType,
        int $subjectId,
        array $details = [],
    ): ModerationLog {
        $log = new ModerationLog;
        $log->fill([
            'actor_user_id' => $actor->moderatorId(),
            'actor_label' => $actor->moderatorLabel(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'details' => $details === [] ? null : $details,
        ]);
        $log->save();

        Log::info('moderation.action', [
            'action' => $action->value,
            'actor' => $actor->moderatorLabel(),
            'actor_user_id' => $actor->moderatorId(),
            'subject_type' => $subjectType->value,
            'subject_id' => $subjectId,
            'details' => $details,
            'moderation_log_id' => $log->id,
        ]);

        return $log;
    }
}
