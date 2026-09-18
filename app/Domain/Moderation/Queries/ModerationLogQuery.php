<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Queries;

use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Moderation\Models\ModerationLog;
use Illuminate\Database\Eloquent\Collection;

/**
 * The audit trail of one subject, newest first.
 */
final class ModerationLogQuery
{
    /**
     * @return Collection<int, ModerationLog>
     */
    public function forSubject(ModerationSubject $subject, int $subjectId): Collection
    {
        return ModerationLog::query()
            ->where('subject_type', $subject)
            ->where('subject_id', $subjectId)
            ->with('actor')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
