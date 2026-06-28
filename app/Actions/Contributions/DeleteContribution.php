<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Models\Contribution;

final class DeleteContribution
{
    public function execute(int $memberId, string $weekStart): ?float
    {
        $contribution = Contribution::query()
            ->where('member_id', $memberId)
            ->whereDate('week_start', $weekStart)
            ->first();

        if ($contribution === null) {
            return null;
        }

        $amount = (float) $contribution->amount;
        $contribution->delete();

        return $amount;
    }
}
