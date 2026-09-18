<?php

declare(strict_types=1);

namespace App\Domain\Claims\Queries;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Claims awaiting an admin decision, oldest first.
 */
final class PendingClaimsQuery
{
    /**
     * @return LengthAwarePaginator<int, VenueClaim>
     */
    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return VenueClaim::query()
            ->where('status', ClaimStatus::Pending)
            ->with(['venue', 'user'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }
}
