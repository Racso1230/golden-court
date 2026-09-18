<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Models;

use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One moderation decision. Append-only: nothing in the application updates
 * or deletes these rows, and there is no updated_at column.
 *
 * @property int $id
 * @property int|null $actor_user_id
 * @property string $actor_label
 * @property ModerationAction $action
 * @property ModerationSubject $subject_type
 * @property int $subject_id
 * @property array<string, mixed>|null $details
 * @property CarbonImmutable $created_at
 * @property-read User|null $actor
 */
#[Fillable(['actor_user_id', 'actor_label', 'action', 'subject_type', 'subject_id', 'details'])]
class ModerationLog extends Model
{
    public const null UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ModerationAction::class,
            'subject_type' => ModerationSubject::class,
            'details' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
