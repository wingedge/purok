<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Models\Contribution;
use App\Models\Member;
use App\Services\ContributionService;

final class RecordContribution
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    public function execute(Member $member, string $weekStart, ?string $remarks = null): Contribution
    {
        return Contribution::updateOrCreate(
            [
                'member_id' => $member->id,
                'week_start' => $weekStart,
            ],
            [
                'amount' => $this->contributions->amountFor($member),
                'remarks' => $remarks,
            ],
        );
    }
}
