<?php

declare(strict_types=1);

namespace App\Domain\Claims\Queries;

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Every claim one user has made, newest first.
 */
final class UserClaimsQuery
{
    public function __construct(private readonly User $user) {}

    /**
     * @return Collection<int, VenueClaim>
     */
    public function get(): Collection
    {
        return VenueClaim::query()
            ->where('user_id', $this->user->id)
            ->with(['venue', 'user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
}
